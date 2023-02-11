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

        //set max Count of content want to search
        $count = 30;
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
    public function gravatarHash($email){
        return md5( strtolower( trim( $email) ) );
    }
}