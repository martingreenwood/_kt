<?php

/**
 * PHP Social Stream 2.5.1
 * Copyright 2015 Axent Media (axentmedia@gmail.com)
 */

class ss_default_slidelayout {
    public $target, $output;
    
    function create_slideitem( $feed_class, $param, $attr = array(), $output = array(), $sbi = 0 ) {
        $iconSocial = ( @$param['icon'][0] ) ? '<img src="'.$param['icon'][0].'" class="origin-flag" style="vertical-align:middle" alt="">' : '<span class="sb-slide-icon sb-' . $feed_class . '"><i class="sb-micon sb-' . $feed_class . '"></i></span>';
        $user_title = (@$param['user']['title']) ? $param['user']['title'] : $param['user']['name'];
        
        $innerthumb = '';
        if (@$param['object'] && @$output['thumb']) {
            $object = str_replace( array("\r\n","\r","\t","\n"), '', $param['object'] );
            $object = htmlentities($object, ENT_QUOTES);
            $thumbdata = ' data-type="object" data-media="' . $object . '" data-size="' . @$param['size'] . '"';
            $innerthumb .= '
            <span class="sb-thumb sb-object"></span>';
        }
        elseif (@$param['thumb'] && @$output['thumb']) {
            $thumb = htmlspecialchars($param['thumb']);
            $thumbdata = ' data-type="' . (@$param['play'] ? 'video' : 'image') . '" data-media="' . $thumb . '" data-size="' . @$param['size'] . '"';
            $innerthumb .= '
            <div class="sb-thumb"></div>';
        } else {
            $nothumb = ' sb-nothumb';
        }

        $inner = '
            <div class="sb-inner"'.@$thumbdata.'>' . $innerthumb;
            
        $inner .= '
                <div class="sb-body'.@$nothumb.'">
                    <div class="sb-scroll'.@$nothumb.'">'.@$iconSocial;
        if (@$param['user']) {
            if (@$param['user']['title'] && @$param['user']['name']) {
                $user_title = @$param['user']['title'];
                $user_text = ( @$param['user']['url'] ) ? '<a href="' . @$param['user']['url'] . '"'.$this->target.'>' . @$param['user']['name'] . '</a>' : @$param['user']['name'];
            } else {
                $user_title = @$param['user']['name'];
                if (@$param['user']['status'])
                    $user_text = ( @$param['url'] ) ? '<a href="' . @$param['url'] . '"'.$this->target.'>' . @$param['user']['status'] . '</a>' : $param['user']['status'];
                else
                    $user_title_style = ' style="padding-top: 5px"';
            }
            if (@$output['user']) {
                $inner .= '
                <div class="sb-user">';
                if (@$param['user']['image']) {
                    $user_image = ( @$param['user']['url'] ) ? '<a href="' . @$param['user']['url'] . '"'.$this->target.'><img alt="' . @$param['user']['name'] . '" src="' . $param['user']['image'] . '"></a>' : '<img alt="' . @$param['user']['name'] . '" src="' . $param['user']['image'] . '">';
                    $inner .= '
    				<div class="sb-uthumb">'.$user_image.'</div>';
                } else {
                    $no_thumb_class = ' sb-nouthumb';
                }
                $user_title_linked = ( @$param['user']['url'] ) ? '<a href="' . @$param['user']['url'] . '"'.$this->target.'>'.$user_title.'</a>' : $user_title;
                $inner .= '
                    <div class="sb-uinfo'.@$no_thumb_class.'">
                        <div class="sb-utitle"'.@$user_title_style.'>' . $user_title_linked . '</div>';
                if (@$user_text)
                    $inner .= '<div class="name">' . $user_text . '</div>';
                $inner .= '
                    </div>
                </div>';
            }
        }
        
        if (@$param['title'] && @$output['title']) {
            $inner .= '
            <span class="sb-title">
                ' . $param['title'] . '
            </span>';
        }

        if ( @$param['text'] && @$output['text'] ) {
            $inner .= '<span class="sb-text">';
            $inner .= @$param['text'];
            $inner .= '</span>';
        }
        
        if (@$param['tags'] && @$output['tags']) {
            $inner .= '
            <span class="sb-text">
                <strong>'.ss_lang( 'tags', 'social-board' ).': </strong>' . $param['tags'] . '
            </span>';
        }
        
        $inner .= @$param['meta'];
        
        $us = '';
        if ( @$param['url'] && @$output['share'] ) {
            if (@$param['share'])
                $us .= $param['share'];
            else {
            $sharetitle = @urlencode( strip_tags($param['title']) );
            $us .= '
                <span class="sb-share">
                    <a class="sb-sicon sb-facebook sb-hover" href="http://www.facebook.com/sharer.php?u=' . urlencode($param['url']) . '&amp;t=' . @$sharetitle . '"'.$this->target.'></a>
                    <a class="sb-sicon sb-twitter sb-hover" href="https://twitter.com/share?url=' . urlencode($param['url']) . '&amp;text=' . @$sharetitle . '"'.$this->target.'></a>
                    <a class="sb-sicon sb-google sb-hover" href="https://plus.google.com/share?url=' . urlencode($param['url']) . '"'.$this->target.'></a>
                    <a class="sb-sicon sb-linkedin sb-hover" href="http://www.linkedin.com/shareArticle?mini=true&amp;url=' . urlencode($param['url']) . '&amp;title=' . @$sharetitle . '"'.$this->target.'></a>
                </span>';
            }
        }
        
        $inner .= '</div>';
        if ($param['date'] && @$output['info'])
        $inner .= '
            <div class="sb-slide-footer">
                <div class="sb-slide-foot">
                ' . $us . '
                <a href="' . @$param['url'] . '"'.$this->target.'>'.ss_lang( 'posted', 'social-board' ).': ' . ss_friendly_date($param['date']) . '</a>
                </div>
            </div>';
                
        $inner .= '
            </div>
        </div>';
        
        $tag = 'div';
        $out1 = '
        <div class="sboard sb-slide sb-modern" id="inline_'.$sbi.'">
            <'.$tag.' class="sb-item sb-' . $feed_class . '">
                ' . $inner;
            $out1 .= '
            </'.$tag.'>
        </div>' . "\n";
        $out = $out1;
        
        return $out;
    }
}
