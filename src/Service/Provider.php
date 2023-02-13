<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class Provider
{
    public function createSearchParams(Request $request){
        $response = [];
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        //set page of content want to search
        $page = 1;
        if(array_key_exists('page',$params))
            $page = $params['page'];
        $response['page'] = $page;

        $cat = '';
        if(array_key_exists('cat',$params))
            $cat = $params['cat'];
        $response['cat'] = $cat;

        //set max Count of content want to search
        $count = 15;
        if(array_key_exists('count',$params))
            $count = $params['count'];
        $response['count'] = $count;

        //set search keyword of content
        $search = '';
        if(array_key_exists('key',$params))
            $search = $params['key'];
        $response['key'] = $search;

        return $response;
    }

    public function maxPages($params,$rowsAllCount){
        $res =  $rowsAllCount / $params['count'];
        return is_float($res) ? (int)$res+1:$res;
    }
    public function gravatarHash($email){
        return md5( strtolower( trim( $email) ) );
    }
}