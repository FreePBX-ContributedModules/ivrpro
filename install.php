<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
global $db, $amp_conf;

if (! function_exists("out")) {
	function out($text) {
		echo $text."<br />";
	}
}

if (! function_exists("outn")) {
	function outn($text) {
		echo $text;
	}
}

$new = $db->query('SELECT * FROM ivrpro_details');
if (DB::IsError($new)) {
	$new = true;
} else {
	$new = false;
}

$sql[] = 'CREATE TABLE IF NOT EXISTS `ivrpro_entries` (
	`id` int(11) default NULL,
	`e_id` int(11) default NULL,
	`grammar` varchar(255) default NULL
	)';

$sql[] = 'CREATE TABLE IF NOT EXISTS `ivrpro_details` (
	`id` int(11) NOT NULL,
	`speech_enabled` tinyint(1) default NULL,
	`pro_directdial` varchar(50) default NULL,
	`pro_timeout_time` int(11) default NULL,
	`pro_announcement` int(11) default NULL,
	`pro_invalid_repeat_loops` varchar(3) default NULL,
	`pro_invalid_repeat_recording` int(11) default NULL,
	`pro_invalid_recording` int(11) default NULL,
	`pro_invalid_destination` varchar(50) default NULL,
	`pro_timeout_repeat_loops` varchar(3) default NULL,
        `pro_timeout_repeat_recording` int(11) default NULL,
        `pro_timeout_recording` int(11) default NULL,
        `pro_timeout_destination` varchar(50) default NULL,
	`pro_retivr` varchar(10) default NULL,
	PRIMARY KEY  (`id`)
	)';

foreach ($sql as $s) {
	$do = $db->query($s);
	if (DB::IsError($do)) {
		out(_('Can not create IVR Pro table: ') . $check->getMessage());
		return false;
	}
}

//
//add retivr field if it doesnt already exists
//
$sql = 'SHOW COLUMNS FROM ivrpro_details LIKE "pro_retivr"';
$res = $db->getAll($sql);
//check to see if the field already exists
if (count($res) == 0) {
	//if not add it
	$sql = 'ALTER TABLE ivrpro_details ADD COLUMN pro_retivr varchar(10) AFTER pro_say_extension';
	$do = $db->query($sql);
	if(DB::IsError($do)) { 
		out(_("cannot add field pro_retivr to table ivrpro_entries \n" . $do->getDebugInfo()));
	} else {
		out(_("pro_retivr added to table ivrpro_entries"));
	}
}

//fix some issues with the table
$sql = 'SHOW COLUMNS FROM ivrpro_details LIKE "pro_directdial"';
$res = $db->getAll($sql);
if (count($res) > 0) {
	$sql = 'ALTER TABLE ivrpro_details MODIFY pro_directdial varchar(50)';
	$do = $db->query($sql);
	if(DB::IsError($do)) {
                out(_("cannot modify field pro_directdial to table ivrpro_entries \n" . $do->getDebugInfo()));
        } else {
                out(_("pro_directdial added to table ivrpro_entries"));
        }
}

$sql = 'SHOW COLUMNS FROM ivrpro_details LIKE "speech_enabled"';
$res = $db->getAll($sql);
if (count($res) > 0) {
        $sql = 'ALTER TABLE ivrpro_details MODIFY speech_enabled tinyint(1)';
        $do = $db->query($sql);
        if(DB::IsError($do)) {
                out(_("cannot modify field speech_enabled to table ivrpro_entries \n" . $do->getDebugInfo()));
        } else {
                out(_("speech_enabled added to table ivrpro_entries"));
        }
}

//
//
//
$sql = 'SHOW COLUMNS FROM ivrpro_entries LIKE "e_id"';
$res = $db->getAll($sql);
//check to see if field already exists
if (count($res) == 1) {
        //if so change it
        $sql = 'ALTER TABLE ivrpro_entries CHANGE e_id e_id varchar(11)';
        $do = $db->query($sql);
        if(DB::IsError($do)) {
                out(_("cannot change field e_id in table ivrpro_entries \n" . $do->getDebugInfo()));
        } else {
                out(_("e_id updated in table ivrpro_entries"));
        }
}

//
// get any ivr not using speech
//
$sql = "SELECT id from ivrpro_entries where grammar != '' group by id";
$res = $db->getAll($sql);
if (count($res) > 0) {
        foreach ($res as $ivr_id) {
                $id[] = $ivr_id[0];
        }
        if (!empty($id)) {
                $sql = "update ivrpro_details SET speech_enabled = 0 where id NOT IN (".implode(',',$id).")";
                $do = $db->query($sql);
                if(DB::IsError($do)) {
                        out(_("Cannot migrate unused speech ivr's at this time.\n" . $do->getDebugInfo()));
                } else {
                        out(_("Successfully migrated unused speech ivr's at this time."));
                }
        }
}
