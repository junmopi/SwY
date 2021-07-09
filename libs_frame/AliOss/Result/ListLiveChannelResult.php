<?php
namespace AliOss\Result;

use AliOss\Model\LiveChannelListInfo;

class ListLiveChannelResult extends Result
{
    protected function parseDataFromResponse()
    {
        $content = $this->rawResponse->body;
        $channelList = new LiveChannelListInfo();
        $channelList->parseFromXml($content);

        return $channelList;
    }
}
