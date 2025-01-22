<?php
 
 /**calculate human readable time */
 if(!function_exists('timeAgo')){
    function timeAgo($timestamp){
        $timeDifference= time() - strtotime($timestamp);
        $seconds=$timeDifference;
        $minutes=round($timeDifference/60);
        $hours=round($timeDifference/3600);
        $days=round($timeDifference/86400);
    
        if($seconds <= 60){
            if($seconds<=1){
                return "an seconds ago";
            }
            return $seconds."s ago";
        } else if($minutes<=60){
            return $minutes."m ago";
        }else if($hours<=24){
            return $hours."h ago";
        }else{
            return date('j M y',strtotime($timestamp));
        }
    }
 }
 