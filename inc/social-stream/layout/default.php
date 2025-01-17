<?php

/**
 * PHP Social Stream 2.5.1
 * Copyright 2015 Axent Media (axentmedia@gmail.com)
 */

class ss_default_layout {
    public $target, $output;
    
    function create_item( $feed_class, $param, $attr = array(), $output = array(), $sbi = 0 ) {
        $playstate = (@$param['play']) ? '<div class="sb-playstate"></div>' : '';
        $datasize = (@$param['size']) ? ' data-size="' . $param['size'] . '"' : '';
        $imglayout = (@$attr['layout_image']) ? ' sb-'.$attr['layout_image'] : '';
        
        $noclass = array();
        if ( ! @$this->output['info'])
            $noclass[] = ' sb-nofooter';
        if ( ! @$param['thumb'] || ! @$this->output['thumb'])
            $noclass[] = ' sb-nothumb';
        if ( count($noclass) > 1 ) {
            $noclass = array();
            $noclass[] = ' sb-noft';
        }
        $inner = '<div class="sb-container'.$imglayout.( implode('', $noclass) ).'">';

        $thumb = $sbthumb = '';
        if (@$attr['carousel']) {
            $cropclass = 'sb-crop';
            if (@$param['iframe'])
                $cropclass .= ' '.$param['iframe'];
            if (@$param['thumb'] && @$output['thumb']) {
				$aurl = (@$param['thumburl'] ? $param['thumburl'] : @$param['url']);
				if (@$param['object'] && @$attr['iframe'] == 'media') {
					$aurl32 = sprintf("%u", crc32($aurl) );
					$aurl = "#$aurl32";
					$thumb .= '
					<div style="display: none">
						<div class="sb-object" id="'.$aurl32.'">
							' . $param['object'] . '
						</div>
					</div>';
				}
                $thumb .= '<a class="'.$cropclass.'" data-original="' . htmlspecialchars($param['thumb']) . '" href="' . $aurl . '"'.$datasize.$this->target.'>'.$playstate.'</a>';
            } else {
                $user_title = (@$param['user']['title']) ? $param['user']['title'] : $param['user']['name'];
                $cropclass .= ' sb-userimg';
                if (@$param['user']['image']) {
                    $thumb .= '<div class="'.$cropclass.'"><img src="' . $param['user']['image'] . '" alt=""><br /><span>'.$user_title.'</span></div>';
                }
            }
            if (@$thumb)
                $sbthumb .= '
                <div class="sb-thumb">
                    ' . $thumb . '
                </div>';
        } else {
            if (@$param['thumb'] && @$output['thumb']) {
				$aurl = htmlspecialchars(@$param['thumburl'] ? $param['thumburl'] : @$param['url']);
				if (@$param['object'] && @$attr['iframe'] == 'media') {
					$aurl32 = sprintf("%u", crc32($aurl) );
					$aurl = "#$aurl32";
					$sbthumb .= '
					<div style="display: none">
						<span class="sb-object" id="'.$aurl32.'">
							' . $param['object'] . '
						</span>
					</div>';
				}
                $iframe = (@$param['iframe']) ? ' class="'.$param['iframe'].'"' : '';
                $sbthumb .= '
                <div class="sb-thumb">
                    <a href="' . $aurl . '"'.@$iframe.$datasize.$this->target.'><img data-original="' . htmlspecialchars($param['thumb']) . '" src="'.SB_PATH.'public/img/oval.svg" alt="">'.$playstate.'</a>
                </div>';
            }
        }
        
        if (@$sbthumb && @$attr['layout_image'] == 'imgexpand') {
            $inner .= $sbthumb;
            $userclass = ' sb-usermini';
        }
        
        $idstr = ' id="'.$sbi.'"';
        if (@$attr['iframe'] == 'slide') {
            $inline = ' data-href="#inline_'.$sbi.'"';
            $sbinline = ' sb-inline';
        } else {
            $inline = $sbinline = '';
        }
        
        $inner .= '
            <div class="sb-inner">';
            
        if (@$param['user'] && @$attr['layout_user'] == 'userpic') {
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
                <div class="sb-user'.@$userclass.'">';
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
                    $inner .= '<div class="sb-uname">' . $user_text . '</div>';
                $inner .= '
                    </div>
                </div>';
            }
        }
        
        if (@$param['title'] && @$output['title'] && ! @$attr['carousel']) {
            $inner .= '
            <span class="sb-title">
                ' . $param['title'] . '
            </span>';
        }

        if (@$sbthumb && @$attr['layout_image'] == 'imgnormal') {
            $inner .= $sbthumb;
        }
        
        if ( (@$param['text'] && @$output['text']) || @$attr['carousel'] ) {
            $expandclass = ( ! @$thumb) ? ' sb-expand' : '';
            $inner .= '<span class="sb-text'.$expandclass.'">';
            
            if (@$attr['carousel']) {
                if (@$param['title'])
                $inner .= '
                <span class="sb-title">
                    ' . @$param['title'] . '
                </span>';
            }
                
            $inner .= $param['text'];
            $inner .= '</span>';
        }
        
        if (!@$attr['carousel']) {
        if (@$param['tags'] && @$output['tags'])
            $inner .= '
            <span class="sb-text">
                <strong>'.ss_lang( 'tags' ).': </strong>' . $param['tags'] . '
            </span>';
        
        $inner .= @$param['meta'];
        }
        $us = '';
        if ( @$param['url'] && @$output['share'] ) {
            if (@$param['share'])
                $us .= $param['share'];
            else {
            $sharetitle = @urlencode( strip_tags($param['title']) );
            $us .= '
                <span class="sb-share">
                    <a class="sb-sicon sb-facebook sb-hover" href="http://www.facebook.com/sharer.php?u=' . urlencode($param['url']) . '&t=' . @$sharetitle . '"'.$this->target.'></a>
                    <a class="sb-sicon sb-twitter sb-hover" href="https://twitter.com/share?url=' . urlencode($param['url']) . '&text=' . @$sharetitle . '"'.$this->target.'></a>
                    <a class="sb-sicon sb-google sb-hover" href="https://plus.google.com/share?url=' . urlencode($param['url']) . '"'.$this->target.'></a>
                    <a class="sb-sicon sb-linkedin sb-hover" href="http://www.linkedin.com/shareArticle?mini=true&url=' . urlencode($param['url']) . '&title=' . @$sharetitle . '"'.$this->target.'></a>
                </span>';
            }
        }
        if ( @$param['user'] && @$output['user'] && @$attr['layout_user'] == 'usernopic' ) {
            $user_text = ( @$param['user']['url'] ) ? '<a href="' . @$param['user']['url'] . '"'.$this->target.'>' . @$param['user']['name'] . '</a>' : @$param['user']['name'];
            $us .= '
            <span class="sb-user-foot">
                <i class="sb-bico sb-user-foot"></i> ' . $user_text . '
            </span>';
        }
        if (@$us)
            $inner .= '
            <div class="sb-info">
                ' . $us . '
            </div>';
        $inner .= '
        </div>';
        
        if ( $attr['type'] == 'timeline' ) {
            $icon = ( @$param['icon'][1] ) ? '<img src="'.$param['icon'][1].'" style="vertical-align:middle" alt="">' : '<i class="sb-icon sb-' . $feed_class . '"></i>';
            $out = '
          <div class="timeline-row"'.$idstr.'>
            <div class="timeline-time">
              <small>'. ss_i18n_date($param['date'], SB_DT_FORMAT) .'</small>'. ss_i18n_date($param['date'], SB_TT_FORMAT) .'
            </div>
            <div class="timeline-icon">
              <div class="bg-' . $feed_class . '">
                ' . $icon . '
              </div>
            </div>
            <div class="timeline-content">
              <div class="panel-body sb-item sb-' . $feed_class . $sbinline . '"'.$inline.'>
              ' . $inner . '
              </div>
            </div>
          </div>
        </div>' . "\n";
        } else {
            $icon = ( @$param['icon'][0] ) ? '<img src="'.$param['icon'][0].'" style="vertical-align:middle" alt="">' : '<i class="sb-icon sb-' . $feed_class . '"></i>';
            $tag = ( $attr['type'] != 'feed' || @$attr['carousel'] ) ? 'div' : 'li';
            
            $out1 = '
            <'.$tag.' class="sb-item sb-' . $feed_class . $sbinline.'"'.$inline.'>
                ' . $inner;
            if ($param['date'] && @$output['info'])
            $out1 .= '
                <div class="sb-footer bg-' . $feed_class . '">
                    ' . $icon . '
                    <a href="' . @$param['url'] . '"'.$this->target.'>'.ss_lang( 'posted' ).': ' . ss_friendly_date($param['date']) . '</a>
                </div>';
            $out1 .= '
            </div>
            </'.$tag.'>' . "\n";
            
            $out = (@$attr['carousel']) ? '<li>'.$out1.'</li>' : $out1;
        }
        return $out;
    }

    function create_colors( $social_colors, $feed_keys, $type, $dotboard, $attr, $themetypeoption ) {
        $style = array();
        
        foreach ($feed_keys as $network) {
            $colorVal = $social_colors[$network];
            if (@$colorVal && @$colorVal != 'transparent') {
                $style[$dotboard.' .bg-'.$network][] = 'background-color:'.$colorVal.' !important';
                
                if (@$attr['lightboxtype'] == 'slideshow')
                    $style['.sb-slide-icon.sb-'.@$network][] = 'background-color: '.$colorVal.' !important';
                    
                $dotfilter = ( $type == 'wall' ) ? str_replace(array('timeline', '.sboard'), array('sb', ''), $dotboard) : $dotboard;
                if (!@$attr['carousel'])
                    $style[$dotfilter.' .sb-'.$network.'.sb-hover:hover, '.$dotfilter.' .sb-'.$network.'.active'][] = 'background-color: '.$colorVal.' !important;border-color: '.$colorVal.' !important;color: #fff !important';
                
                // set colors for tabs
                if (@$attr['tabable']) {
                    $style["$dotboard.tabable .sb-tabs .sticky .".$network.":hover, $dotboard.tabable .sb-tabs .sticky .".$network.".active"][] = 'background-color: '.$colorVal;
                }
            }
        }
        
        // set item background color
        if ( @$themetypeoption['item_background_color'] ) {
            if ( $themetypeoption['item_background_color'] != 'transparent') {
                $style["$dotboard .sb-item .sb-container .sb-inner"][] = 'background-color: '.$themetypeoption['item_background_color'];
            }
        }
        // set item border
        if ( $border_size = @$themetypeoption['item_border_size'] ) {
            if ( $border_size > 1 ) {
                $border_radius = 3+$border_size-1;
                $style["$dotboard .sb-item .sb-container"][] = 'border-radius: '.$border_radius.'px;-moz-border-radius: '.$border_radius.'px;-webkit-border-radius: '.$border_radius.'px';
            }
        }
        if ( @$themetypeoption['item_border_color'] ) {
            if ( $themetypeoption['item_border_color'] != 'transparent') {
                $dontbordersize = true;
                $style["$dotboard .sb-item .sb-container"][] = 'border: '.@$themetypeoption['item_border_size'].'px solid '.$themetypeoption['item_border_color'];
                $style["$dotboard .sb-item .sb-container"][] = 'background-color: '.$themetypeoption['item_border_color'];
            }
        }
        if ( @$themetypeoption['item_border_size'] && ! @$dontbordersize ) {
            $style["$dotboard .sb-item .sb-container"][] = 'border-width: '.@$themetypeoption['item_border_size'].'px';
        }
        
        return $style;
    }
}
?>