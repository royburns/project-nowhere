<?php

define("OP_TO_FILE", 1);              // Output to file
define("OP_OUTPUT", 2);               // Output to browser
define("OP_NOT_KEEP_SCALE", 4);       // Free scale
define("OP_BEST_RESIZE_WIDTH", 8);    // Scale to width
define("OP_BEST_RESIZE_HEIGHT", 16);  // Scale to height

define("CM_DEFAULT",0);               // Clipping method: default
define("CM_LEFT_OR_TOP",1);           // Clipping method: left or top
define("CM_MIDDLE",2);                // Clipping method: middle
define("CM_RIGHT_OR_BOTTOM",3);       // Clipping method: right or bottom

/**
 * ImageCore::vxResize
 *
 * @param string $srcFile source file
 * @param string $srcFile destination file
 * @param int $dstW width of destination file (pixel)
 * @param int $dstH height of destination file (pixel)
 * @param int $option options, you add GlobalCore::multiple options like 1+2(or 1|2), this utilize function 1 & 2
 *      1: default，output to file 2: GlobalCore::output to browser stream 4: free scale
 *      8：scale to width 16：scale to height
 * @param int $cutmode clipping method 0: default 1: left or top 2: middle 3: right or bottom
 * @param int $startX start X axis (pixel)
 * @param int $startY start Y axis (pixel)
 * @return array return[0]=0: OK; return[0] error code return[1] string: error description
 */
	
class ImageCore{
    public static function vxResize($srcFile, $dstFile, $dstW, $dstH, $option=OP_TO_FILE, $cutmode=CM_DEFAULT, $quality=90, $startX=0, $startY=0) {
        $img_type = array(1=>"gif", 2=>"jpeg", 3=>"png");
        $type_idx = array("gif"=>1, "jpg"=>2, "jpeg"=>2, "jpe"=>2, "png"=>3);

        if (!file_exists($srcFile)) {
            return array(-1, "Source file not exists: $srcFile.");
        }

        $path_parts = @pathinfo($dstFile);
        $ext = strtolower ($path_parts["extension"]);

        if ($ext == "") {
            return array(-5, "Can't detect GlobalCore::output image's type.");
        }

        $func_output = "image" . $img_type[$type_idx[$ext]];

        if (!function_exists ($func_output)) {
            return array(-2, "Function not exists for GlobalCore::output：$func_output.");
        }

        $data = @GetImageSize($srcFile);
        $func_create = "imagecreatefrom" . $img_type[$data[2]];

        if (!function_exists ($func_create)) {
            return array(-3, "Function not exists for create：$func_create.");
        }

        $im = @$func_create($srcFile);

        $srcW = @ImageSX($im);
        $srcH = @ImageSY($im);
        $srcX = 0;
        $srcY = 0;
        $dstX = 0;
        $dstY = 0;

        if ($option & OP_BEST_RESIZE_WIDTH) {
            $dstH = round($dstW * $srcH / $srcW);
        }

        if ($option & OP_BEST_RESIZE_HEIGHT) {
            $dstW = round($dstH * $srcW / $srcH);
        }

        $fdstW = $dstW;
        $fdstH = $dstH;

        if ($cutmode != CM_DEFAULT) { // clipping method 1: left or top 2: middle 3: right or bottom

            $srcW -= $startX;
            $srcH -= $startY;

            if ($srcW*$dstH > $srcH*$dstW) { 
                $testW = round($dstW * $srcH / $dstH);
                $testH = $srcH;
            } else {
                $testH = round($dstH * $srcW / $dstW);
                $testW = $srcW;
            }

            switch ($cutmode) {
            case CM_LEFT_OR_TOP: $srcX = 0; $srcY = 0; break;
            case CM_MIDDLE: $srcX = round(($srcW - $testW) / 2);
            $srcY = round(($srcH - $testH) / 2); break;
        case CM_RIGHT_OR_BOTTOM: $srcX = $srcW - $testW;
            $srcY = $srcH - $testH;
            }

            $srcW = $testW;
            $srcH = $testH;
            $srcX += $startX;
            $srcY += $startY;

        } else {
            if (!($option & OP_NOT_KEEP_SCALE)) {
                if ($srcW*$dstH>$srcH*$dstW) { 
                    $fdstH=round($srcH*$dstW/$srcW); 
                    $dstY=floor(($dstH-$fdstH)/2); 
                    $fdstW=$dstW;
                } else { 
                    $fdstW=round($srcW*$dstH/$srcH); 
                    $dstX=floor(($dstW-$fdstW)/2); 
                    $fdstH=$dstH;
                }

                $dstX=($dstX<0)?0:$dstX;
                $dstY=($dstX<0)?0:$dstY;
                $dstX=($dstX>($dstW/2))?floor($dstW/2):$dstX;
                $dstY=($dstY>($dstH/2))?floor($dstH/s):$dstY;

            }
        }

        if( function_exists("imagecopyresampled") and 
            function_exists("imagecreatetruecolor") ){
                $func_create = "imagecreatetruecolor";
                $func_resize = "imagecopyresampled";
            } else {
                $func_create = "imagecreate";
                $func_resize = "imagecopyresized";
            }

        $newim = @$func_create($dstW,$dstH);
        $black = @ImageColorAllocate($newim, 0,0,0);
        $back = @imagecolortransparent($newim, $black);
        @imagefilledrectangle($newim,0,0,$dstW,$dstH,$black);
        @$func_resize($newim,$im,$dstX,$dstY,$srcX,$srcY,$fdstW,$fdstH,$srcW,$srcH);

        if ($option & OP_TO_FILE) {
            switch ($type_idx[$ext]) {
            case 1:
                case 3:
                    @$func_output($newim,$dstFile);
                    break;
                case 2:
                    @$func_output($newim,$dstFile,$quality);
                    break;
            }
        }

        if ($option & OP_OUTPUT) {
            if (function_exists("headers_sent")) {
                if (headers_sent()) {
                    return array(-4, "HTTP already sent, can't GlobalCore::output image to browser.");
                }
            }
            header("Content-type: image/" . $img_type[$type_idx[$ext]]);
            @$func_output($newim);
        }

        @imagedestroy($im);
        @imagedestroy($newim);

        return array(0, "OK");
    }

    public static function mkdir_by_hash($s, $dir = '.') {
        $s = md5($s);
        $dir .= "/{$s[0]}/{$s[1]}/{$s[2]}";
        GlobalCore::mkdirs($dir);
        //!is_dir($dir.'/'.$s[0]) && mkdir($dir.'/'.$s[0], 0777);
        //!is_dir($dir.'/'.$s[0].'/'.$s[1]) && mkdir($dir.'/'.$s[0].'/'.$s[1], 0777);
        //!is_dir($dir.'/'.$s[0].'/'.$s[1].'/'.$s[2]) && mkdir($dir.'/'.$s[0].'/'.$s[1].'/'.$s[2], 0777);
        return $s[0].'/'.$s[1].'/'.$s[2];
    }

    public static function GrabImage($url,$filename="") {
        if($url==""):return false;endif; 
        if($filename=="") { 
            $ext=strrchr($url,"."); 
            if($ext!=".gif" && $ext!=".jpg"):return false;endif;
            $filename=date("dMYHis").$ext; 
        } 
        ob_start(); 
        readfile($url); 
        $img = ob_get_contents();
        ob_end_clean(); 
        $size = strlen($img);  
        $fp2=@fopen($filename, "w+");
        fwrite($fp2,$img); 
        fclose($fp2); 
        return $filename; 
    } 

    public static function getIMGext($avatar) {
        $avatarext = strtolower(GlobalCore::fileext($avatar));
        if ($avatarext =='jpg'){
            $img_creat_mode = imagecreatefromjpeg($avatar);
        } elseif ($avatarext =='gif') {
            $img_creat_mode = imagecreatefromgif($avatar);
        } elseif ($avatarext =='png') {
            $img_creat_mode = imagecreatefrompng($avatar);
        }
        return $img_creat_mode;
    }

    public static function getRgbFromGd($color_hex) {
        return array_map('hexdec', explode('|', wordwrap(substr($color_hex, 1), 2, '|', 1)));
    }

    public static function AttachPhoto($input_name,$entry_id) {
        global $db, $nw_uid, $tablepre, $timestamp, $adminid;
        if(isset($_POST[$input_name])) {
            $photo_sql = implode(',', array_unique($_POST[$input_name]));
            $db->query("UPDATE {$tablepre}blog_photo SET photo_eid='0' WHERE photo_eid = '{$entry_id}'");
            $db->query("UPDATE {$tablepre}blog_photo SET photo_eid='{$entry_id}' WHERE photo_id IN ($photo_sql)");
            $icon_id =  intval(trim($_POST[$input_name][0]));
            $entry_icon = $db->fetch_first("SELECT photo_target FROM {$tablepre}blog_photo WHERE photo_id='$icon_id'");
            $db->query("UPDATE {$tablepre}blog_entry SET entry_icon='{$entry_icon['photo_target']}' WHERE entry_id = '$entry_id'");
        } else {
            $db->query("UPDATE {$tablepre}blog_photo SET photo_eid='0' WHERE photo_eid = '{$entry_id}'");
        }
    }

    public static function RecvPortraits($input_name, $img_id, $dir, $hash_type = 'null') {
        if (GlobalCore::disuploadedfile($_FILES[$input_name]['tmp_name']) && $_FILES[$input_name]['tmp_name'] != 'none' && $_FILES[$input_name]['tmp_name'] && trim($_FILES[$input_name]['name'])) {
            $pic_extarray = array('gif', 'jpg', 'png');
            $_FILES[$input_name]['name'] = GlobalCore::chobits_addslashes($_FILES[$input_name]['name']);
            $pic_ext = strtolower(GlobalCore::fileext($_FILES[$input_name]['name']));
            if(is_array($pic_extarray) && !in_array($pic_ext, $pic_extarray)) {
                GlobalCore::showmessage('profile_avatar_invalid');
            }

            if ($hash_type == 'id') {
                $filename = $img_id;
                $pic = $dir.'/l/'.GlobalCore::mkdir_by_uid($img_id,NOWHERE_ROOT.$dir.'/l').'/'.$filename.'.'.$pic_ext;
            } else {
                $filename = $img_id.'_'.GlobalCore::random(5);
                $pic = $dir.'/l/'.GlobalCore::mkdir_hash($img_id,NOWHERE_ROOT.$dir.'/l').'/'.$filename.'.'.$pic_ext;
            }

            $pic_target = NOWHERE_ROOT.'./'.$pic;
            if(!@copy($_FILES[$input_name]['tmp_name'], $pic_target)) {
                @move_uploaded_file($_FILES[$input_name]['tmp_name'], $pic_target);
            }
            if(file_exists($pic_target)) {
                $port['pic'] = $pic;
                $port['filename'] = $filename;
                $port['pic_target'] = $pic_target;
                $port['pic_ext'] = $pic_ext;
                return $port;
            }

        }
    }

    public static function GenUserPortraits($pic,$filename,$pic_target,$pic_ext) {
        global $nw_uid;
        $img_info = getimagesize($pic_target);
        $pic_d = AVATAR_DIR.'/l/'.GlobalCore::mkdir_by_uid($nw_uid,NOWHERE_ROOT.AVATAR_DIR.'/l').'/'.$filename.'.jpg';
        $pic_m = AVATAR_DIR.'/m/'.GlobalCore::mkdir_by_uid($nw_uid,NOWHERE_ROOT.AVATAR_DIR.'/m').'/'.$filename.'.jpg';
        $pic_s = AVATAR_DIR.'/s/'.GlobalCore::mkdir_by_uid($nw_uid,NOWHERE_ROOT.AVATAR_DIR.'/s').'/'.$filename.'.jpg';
        @ImageCore::vxResize($pic,$pic_d,75, 75, 1|4, 2);
        @ImageCore::vxResize($pic,$pic_m,48, 48, 1|4, 2);
        @ImageCore::vxResize($pic,$pic_s,32, 32, 1|4, 2);
        if ($pic_ext != 'jpg') {
            @unlink($pic_target);
        }
    }
}
