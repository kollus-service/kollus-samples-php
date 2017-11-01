<?php

/**
 * @param object $blockInfo
 * @return array
 */
function importFromBlockInfo($blockInfo)
{
    $progressBlocks = array();
    if (isset($blockInfo->block_count) && isset($blockInfo->blocks)) {
        for ($i = 0; $i < $blockInfo->block_count; $i++) {
            $progressBlocks[$i] = array(
                'is_read_block' => isset($blockInfo->blocks->{'b'.$i}) ? (bool)$blockInfo->blocks->{'b'.$i} : false,
                'time' => isset($blockInfo->blocks->{'t'.$i}) ? (int)$blockInfo->blocks->{'t'.$i} : 0,
                'percent' => isset($blockInfo->blocks->{'p'.$i}) ? (int)$blockInfo->blocks->{'p'.$i} : 0,
            );
        }
    }

    return $progressBlocks;
}

/**
 * @param array $progressBlocks
 * @return object
 */
function exportToBlockinfo($progressBlocks)
{
    $blockInfo = (object)array(
        'block_count' => count($progressBlocks),
        'blocks' => (object)array(),
    );

    for ($i = 0; $i < $blockInfo->block_count; $i++) {
        $blockInfo->blocks->{'b'.$i} = (int)$progressBlocks[$i]['is_read_block'];
        $blockInfo->blocks->{'t'.$i} = $progressBlocks[$i]['time'];
        $blockInfo->blocks->{'p'.$i} = $progressBlocks[$i]['percent'];
    }

    return $blockInfo;
}

/**
 * Important : Maybe DB is database wrapper static class.
 *             schema.sql
 */

if (isset($_POST['json_data'])) {
    $data = json_decode($_POST['json_data']);
    $userInfo = $data->user_info;
    $contentInfo = $data->content_info;
    $blockInfo = $data->block_info;

    $newProgressBlocks = importFromBlockInfo($blockInfo);
    $startAt = isset($contentInfo->start_at) ? $contentInfo->start_at : null;
    $playtime = isset($contentInfo->playtime) ? $contentInfo->playtime : 0;

    $mediaContentKey = isset($contentInfo->media_content_key) ? $contentInfo->media_content_key : null;
    $clientUserId = isset($userInfo->client_user_id) ? $userInfo->client_user_id : null;
    $playerId = isset($userInfo->player_id) ? $userInfo->player_id : null;

    $video = DB::table('videos')
        ->findOrCreate(array('media_content_key' => $mediaContentKey));

    $user = DB::table('users')
        ->findOrCreate(array('client_user_id' => $clientUserId));

    /**
     * TODO : findOrCreate at progress_relations table
     */
    $progressRelation = DB::table('progress_relations')
        ->findOrCreate(array(
            'video_id' => $video->id,
            'user_id' => $user->id,
        ))
    ;
    $oldProgressBlocks = empty($progressRelation->progress_block_info) ? array() :
        importFromBlockInfo(json_decode($progressRelation->progress_block_info));

    /**
     * TODO : updateOrInsert at progress_datas table
     */
    DB::table('progress_datas')
        ->updateOrInsert(
            array(
                'progress_relation_id' => $progressRelation->id,
                'start_at' => $startAt,
            ), // where
            array(
                'progress_block_info' => json_encode(exportToBlockinfo($newProgressBlocks)),
                'playtime' => $playtime,
                'player_id' => $playerId,
                'updated_at' => time(),
            ) // insert or update values
        )
    ;

    $updateProgressBlocks = array();
    foreach ($newProgressBlocks as $progressIndex => $newProgressBlock) {
        $oldProgressBlock = $oldProgressBlocks[$progressIndex];
        if (!array_key_exists($progressIndex, $oldProgressBlocks)) {
            $updateProgressBlocks[$progressIndex] = array(
                'is_read_block' => false,
                'time' => 0,
                'percent' => 0,
            );
        } else {
            $updateProgressBlocks[$progressIndex] = $oldProgressBlock;
        }

        if ($newProgressBlock['is_read_block']) {
            $updateProgressBlocks[$progressIndex]['is_read_block'] = 1;
        }

        // max time
        if ($oldProgressBlock['time'] < $newProgressBlock['time']) {
            $updateProgressBlocks[$progressIndex]['time'] = $newProgressBlock['time'];
        }

        // max percent
        if ($oldProgressBlock['percent'] < $newProgressBlock['percent']) {
            $updateProgressBlocks[$progressIndex]['percent'] = $newProgressBlock['percent'];
        }
    }

    $blockCount = count($updateProgressBlocks);
    $isReadBlockCount = 0;
    foreach ($updateProgressBlocks as $updateProgressBlock) {
        if ($updateProgressBlock['is_read_block']) {
            $isReadBlockCount++;
        }
    }
    $progressValue = $blockCount > 0 ? $isReadBlockCount / $blockCount : 0;

    /**
     * TODO : update progress_relations table
     */
    DB::table('progress_relations')
        ->where(array('id' => $progressRelation->id))
        ->update(array(
            'progress_block_info' => json_encode(exportToBlockinfo($updateProgressBlocks)),
            'progress_values' => $progressValue,
            'start_at' => $startAt,
            'updated_at' => time(),
        ))
    ;
}
