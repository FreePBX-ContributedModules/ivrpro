<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
function ivrpro_configpageload() {
	global $currentcomponent, $display;

	if ($display == 'ivr' && (isset($_REQUEST['action']) && $_REQUEST['action']=='add'|| isset($_REQUEST['id']) && $_REQUEST['id'] != '')) {
		//set values or defualt them
		$deet = array('speech_enabled', 'pro_announcement', 'pro_invalid_repeat_loops', 'pro_invalid_repeat_recording', 'pro_invalid_recording',
							'pro_invalid_destination', 'pro_timeout_repeat_loops', 'pro_timeout_repeat_recording',
							'pro_timeout_recording','pro_timeout_destination', 'pro_directdial', 'id', 'pro_retivr', 'pro_timeout_time');
		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add') {
			foreach ($deet as $d) {
				switch ($d){
					case 'pro_timeout_time':
						$dir[$d] = 10;
						break;
					case 'pro_invalid_repeat_loops':
					case 'pro_timeout_repeat_loops';
						$dir[$d] = 3;
						break;
					case 'speech_enabled':
						$dir[$d] = false;
						break;
					case 'pro_announcement':
						$dir[$d] = '';
						break;
					case 'pro_invalid_recording':
					case 'pro_timeout_recording':
					case 'pro_timeout_repeat_recording':
					case 'pro_invalid_repeat_recording':
						$dir[$d] = 0;
						break;
					default:
		      				$dir[$d] = '';
						break;
				}
			}
		} else {
			$dir = ivrpro_get_dir_details($_REQUEST['id']);
			if (!$dir) {
				foreach ($deet as $d) {
					switch ($d){
						case 'pro_timeout_time':
							$dir[$d] = 10;
							break;
						case 'pro_invalid_repeat_loops';
						case 'pro_timeout_repeat_loops';
							$dir[$d] = 3;
							break;
						case 'speech_enabled':
							$dir[$d] = true;
							break;
						case 'pro_timeout_destination':
						case 'pro_invalid_destination':
							$dir[$d] = 'ivr,' . $_REQUEST['id'] . ',1';
							break;
						case 'pro_announcement':
							$dir[$d] = '';
							break;
                                        	case 'pro_invalid_recording':
                                        	case 'pro_timeout_recording':
                                        	case 'pro_timeout_repeat_recording':
                                        	case 'pro_invalid_repeat_recording':
							$dir[$d] = 0;
							break;
						default:
			      				$dir[$d] = '';
							break;
					}
				}
			}

		}
	$section = _('IVR Pro Options (SPEECH)');
	//generate page
	//generate recording dropdown
	$currentcomponent->addoptlistitem('recordings', 0, _('Default'));
	foreach(recordings_list() as $r){
		$currentcomponent->addoptlistitem('recordings', $r['id'], $r['displayname']);
	}

	//build repeat_loops select list and defualt it to 3
	$currentcomponent->addoptlistitem('repeat_loops', 'disabled', 'Disabled');
	for($i = 1; $i < 11; $i++){
		$currentcomponent->addoptlistitem('repeat_loops', $i, $i);
	}
        $currentcomponent->setoptlistopts('recordings', 'sort', false);

	$currentcomponent->addguielem($section, new gui_checkbox('speech_enabled', $dir['speech_enabled'],
									_('Speech Enabled'), _('Allow this ivr to use speech recognition.'),true));
	$currentcomponent->addguielem($section, new gui_selectbox('pro_announcement', $currentcomponent->getoptlist('recordings'),
                                                                        $dir['pro_announcement'], _('Announcement'), _('Greeting to be played on entry to the  speech ivr'), false));
	$currentcomponent->addguielem($section, new gui_selectbox('pro_directdial', $currentcomponent->getoptlist('directdial'),
                							$dir['pro_directdial'], _('Direct Dial'), _('Provides options for callers to direct dial an extension. Direct dialing can be:'). ul($currentcomponent->getgeneralarray('directdial_help')), false));
        $currentcomponent->addguielem($section, new guielement('pro_timeout_time', '<tr class="IVRProOptionsSPEECH"><td>' . fpbx_label(_('Timeout'), _('Amount of time to be concidered a timeout')).'</td><td><input type="number" name="pro_timeout_time" value="'
                                        				. $dir['pro_timeout_time'] .'" required></td></tr>'));
	$currentcomponent->addguielem($section, new gui_selectbox('pro_invalid_repeat_loops',
									$currentcomponent->getoptlist('repeat_loops'), $dir['pro_invalid_repeat_loops'], _('Invalid Retries'),
									_('Number of times to retry when receiving an invalid/unmatched response from the caller'), false));
	$currentcomponent->addguielem($section, new gui_selectbox('pro_invalid_repeat_recording',
									$currentcomponent->getoptlist('recordings'), $dir['pro_invalid_repeat_recording'], _('Invalid Retry Recording'),
									_('Prompt to be played when an invalid/unmatched response is received, before prompting the caller to try again'), false));
	$currentcomponent->addguielem($section, new gui_selectbox('pro_invalid_recording',
									$currentcomponent->getoptlist('recordings'), $dir['pro_invalid_recording'], _('Invalid Recording'),
									_('Prompt to be played before sending the caller to an alternate destination due to receiving the maximum amount of invalid/unmatched responses (as determined by Invalid Retries)'), false));
	$currentcomponent->addguielem($section, new gui_drawselects('pro_invalid_destination', 'proinvalid',
									$dir['pro_invalid_destination'], _('Invalid Destination'),
									_('Destination to send the call to after Invalid Recording is played. You should consider setting the DMTF version of this ivr to use in case the speech version doesn\'t work.'), false));
	$currentcomponent->addguielem($section, new gui_selectbox('pro_timeout_repeat_loops',
                                                                        $currentcomponent->getoptlist('repeat_loops'), $dir['pro_timeout_repeat_loops'], _('Timeout Retries'),
                                                                        _('Number of times to retry when receiving an invalid/unmatched response from the caller'), false));
        $currentcomponent->addguielem($section, new gui_selectbox('pro_timeout_repeat_recording',
                                                                        $currentcomponent->getoptlist('recordings'), $dir['pro_timeout_repeat_recording'], _('Timeout Retry Recording'),
                                                                        _('Prompt to be played before sending the caller to an alternate destination due to the caller pressing 0 or receiving the maximum amount of invalid/unmatched responses (as determined by Invalid Retries)'), false));
        $currentcomponent->addguielem($section, new gui_selectbox('pro_timeout_recording',
                                                                        $currentcomponent->getoptlist('recordings'), $dir['pro_timeout_recording'], _('Timeout Recording'),
                                                                        _('Prompt to be played before sending the caller to an alternate destination due to receiving the maximum amount of invalid/unmatched responses (as determined by Invalid Retries)'), false));
        $currentcomponent->addguielem($section, new gui_drawselects('pro_timeout_destination', 'protimeout',
                                                                        $dir['pro_timeout_destination'], _('Timeout Destination'),
                                                                        _('Destination to send the call to after Invalid Recording is played.'), false));
	$currentcomponent->addguielem($section, new gui_checkbox('pro_retivr',
									$dir['pro_retivr'], _('Return to IVR after VM'), _('If checked, upon exiting voicemail a caller will be returned to this IVR if they got a users voicemail')));
	//set default ivr pro invalid destination to the dmtf
	$html = <<<EOD
	<script type="text/javascript">
	$(document).ready(function(){
        	$('[name=frm_ivr]').submit(function(){
                	//set timeout/invalid destination, removing hidden field if there is no valus being set
                	if ($('#pro_invalid_repeat_loops').val() != 'disabled') {
                	        pro_invalid = $('[name=' + $('[name=gotoproinvalid]').val() + 'proinvalid]').val();
                	        $('#pro_invalid_destination').val(pro_invalid);
                	} else {
                	        $('#pro_invalid_destination').remove();
                	}

                	if ($('#pro_timeout_repeat_loops').val() != 'disabled') {
                	        pro_timeout = $('[name=' + $('[name=gotoprotimeout]').val() + 'protimeout]').val();
                	        $('#pro_timeout_destination').val(pro_timeout);
                	} else {
                	        $('#pro_timeout_destination').remove();
                	}

        	})


        	//show/hide invalid elements on change
        	$('#pro_invalid_repeat_loops').change(pro_invalid_elements)

        	//show/hide timeout elements on change
        	$('#pro_timeout_repeat_loops').change(pro_timeout_elements)
	});
	//always disable hidden elements so that they dont trigger validation
	function pro_invalid_elements() {
        	var invalid_elements = $('#pro_invalid_repeat_recording, #pro_invalid_recording, #pro_invalid_destination, [name=gotoproinvalid]');
        	var invalid_element_tr = invalid_elements.parent().parent();
        	switch ($('#pro_invalid_repeat_loops').val()) {
        	        case 'disabled':
        	                invalid_elements.attr('disabled', 'disabled')
        	                invalid_element_tr.hide()
        	                break;
        	        case '0':
        	                invalid_elements.removeAttr('disabled')
        	                invalid_element_tr.show();
        	                $('#pro_invalid_repeat_recording').parent().parent().hide();
        	                break;
        	        default:
        	                invalid_elements.removeAttr('disabled')
        	                invalid_element_tr.show()
        	                break;
        	}
	}

	//always disable hidden elements so that they dont trigger validation
	function pro_timeout_elements() {
        	var timeout_elements = $('#pro_timeout_repeat_recording, #pro_timeout_recording, #pro_timeout_destination, [name=gotoprotimeout]');
        	var timeout_element_tr = timeout_elements.parent().parent();
        	switch ($('#pro_timeout_repeat_loops').val()) {
        	        case 'disabled':
        	                timeout_elements.attr('disabled', 'disabled')
        	                timeout_element_tr.hide()
        	                break;
        	        case '0':
        	                timeout_elements.removeAttr('disabled')
        	                timeout_element_tr.show();
        	                $('#pro_timeout_recording').parent().parent().hide();
        	        default:
        	                timeout_elements.removeAttr('disabled')
        	                timeout_element_tr.show()
        	                break;
        	}
	}

	</script>
EOD;
	$currentcomponent->addguielem($section, new guielement('rawhtml', $html, ''));
	}
}

function ivrpro_configpageinit($pagename) {
	global $currentcomponent;
	if($pagename == 'ivr'){
			$currentcomponent->addprocessfunc('ivrpro_configprocess');
			$currentcomponent->addguifunc('ivrpro_configpageload');
    	return true;
	}

}

//prosses received arguments
function ivrpro_configprocess(){
	if ($_REQUEST['display'] == 'ivr') {
		global $db, $amp_conf;
		//get variables for ivrpro_details
		$requestvars = array('id', 'speech_enabled', 'pro_directdial', 'pro_timeout_time', 'pro_announcement', 'pro_invalid_repeat_loops',
							'pro_invalid_repeat_recording', 'pro_invalid_recording', 'pro_invalid_destination',
							'pro_timeout_repeat_loops',  'pro_timeout_repeat_recording', 'pro_timeout_recording',
							'pro_timeout_destination', 'pro_retivr');

		foreach($requestvars as $var){
			$vars[$var] = isset($_REQUEST[$var]) ? $_REQUEST[$var] : '';
		}
		//these need to stay out of the array, otherwise they get passed in to the wrong places
		$entries	= isset($_REQUEST['entries']) ? $_REQUEST['entries'] : '';
		$action 	= isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
		//dbug('request', $_REQUEST);
		switch($action){
			case 'save':
				if (!$vars['id']) {
					$sql = 'SELECT' . ( ($amp_conf["AMPDBENGINE"]=="sqlite3") ? ' last_insert_rowid()' : ' LAST_INSERT_ID()' );
					$vars['id'] = $db->getOne($sql);
					if ($db->IsError($vars['id'])){
						die_freepbx($vars['id']->getDebugInfo());
					}
				}
				//TODO: Speak with mbrevda as this doesn't follow the way the new IVR 2.10 module works
				//set a valid failover destination if one wasnt selected yet, defaulting
				//to this ivr's dmtf options
				if ($vars['pro_invalid_destination'] == 'DTMF') {
					$vars['pro_invalid_destination'] = 'ivr-' . $vars['id'] . ',s,1';
				}
				if ($vars['pro_timeout_destination'] == 'DTMF') {
                                        $vars['pro_timeout_destination'] = 'ivr-' . $vars['id'] . ',s,1';
                                }
				ivrpro_save_dir_details($vars);
				ivrpro_save_dir_entries($vars['id'], $entries);
			break;
			case 'delete':
				ivrpro_delete($vars['id']);
			break;
		}
	}
}

function ivrpro_get_config($engine) {
	global $ext, $db, $amp_conf;
	switch ($engine) {
		case 'asterisk':
			$sql = 'SELECT * FROM ivrpro_details WHERE speech_enabled = "1" ORDER BY id';
			$results = sql($sql, 'getAll', DB_FETCHMODE_ASSOC);
			if (!$results) {
				break;//nothing to do!
			}
				break;
			}
			foreach($results as $ivr) {
				$c = 'ivrpro-' . $ivr['id'];

				$ext->addSectionComment($c, $ivr['name'] ? $ivr['name'] : 'IVR Pro ' . $ivr['id']);

                                if ($ivr['pro_directdial']) {
                                        if ($ivr['pro_directdial'] == 'ext-local') {
                                                $ext->addInclude($c, 'from-did-direct-ivr'); //generated in core module
                                       		$directDialContext = 'from-did-direct-ivr';
					 } else {
                                                //generated by directory
                                                $ext->addInclude($c, 'from-ivr-directory-' . $ivr['pro_directdial']);
                                                $directdial_contexts[$ivr['pro_directdial']] = $ivr['pro_directdial'];
                                        	$directDialContext = 'from-ivr-directory-' . $ivr['pro_directdial'];
					}
                                }
				//$ext->add($c, 's', '', new ext_noop());
				$ext->add($c, 's', '', new ext_answer(''));
				$ext->add($c, 's', '', new ext_wait('1'));//greeting promt can sometimes get cut off if answered to fast
				$ext->add($c, 's', '', new ext_setvar('LOOPCOUNTER', '0'));
                		$ext->add($c, 's', '', new ext_setvar('INVALID_COUNT', '0'));

				//load grammar file and promt user for speech
				$ext->add($c, 's', 'start', new ext_noop('Starting ivrpro :: {LOOPCOUNTER} = ${LOOPCOUNTER}'));
				$ext->add($c, 's', '', new ext_setvar('SPEECH_DTMF_TERMINATOR', '#'));
				$ext->add($c, 's', '', new ext_setvar('SPEECH_DTMF_MAXLEN', '10'));
				$ext->add($c, 's', '', new ext_setvar('LOOPCOUNTER', '$[${LOOPCOUNTER}+1]'));
				$ext->add($c, 's', '', new ext_execif('$["${SPEECH(status)}" = "1"]', 'SpeechDestroy'));
				$ext->add($c, 's', '', new ext_speechcreate(''));
				$ext->add($c, 's', '', new ext_gosubif('$["${ERROR}" = "1"]', 'no-ports,1'));

				//in case we have no grammars, dont make it possible to get to the grammar file or lumenvox chokes
				$grammar_count = ivrpro_get_grammar_count($ivr['id']);
				if ($grammar_count < 1) {
					$ext->add($c, 's', '', new ext_goto(1,'s','ivr-'.$ivr['id']));
				}

				if (class_exists('ext_tryexec')) {
					$tryLoadGrammar = new ext_speechloadgrammar('ivr' . $ivr['id'], '/etc/asterisk/ivrpro/ivr-${GRAMFILE}.gram');
                                	$tryLoadGrammar = $tryLoadGrammar->output();
                                	$ext->add($c, 's', '', new ext_tryexec($tryLoadGrammar));
					$ext->add($c, 's', '', new ext_gotoif('$["${TRYSTATUS}" != "SUCCESS"]', 'ivr-'.$ivr['id'].',s,1'));
				} else {
					$ext->add($c, 's', '', new ext_speechloadgrammar('ivr' . $ivr['id'], '/etc/asterisk/ivrpro/ivr-${GRAMFILE}.gram'));
				}
				$ext->add($c, 's', '', new ext_speechactivategrammar('ivr' . $ivr['id']));

				//only play the welcom message on the first loop
                		$ext->add($c, 's', '', new ext_gotoif('$["$[${LOOPCOUNTER} - ${INVALID_COUNT}]" > "${TIMEOUT_RETRY}"]', 'timeout,1'));
                		$ext->add($c, 's', '', new ext_gotoif('$["${INVALID_COUNT}" > "${INVALID_RETRY}"]', 'invalid,1'));
				$ext->add($c, 's', '', new ext_execif('$["${LOOPCOUNTER}" = "1"]', 'SpeechBackground', '${WELCOM_REC},${TIMEOUT}'));
                		$ext->add($c, 's', '', new ext_execif('$["${SPEECH(results)}" = "0" && "${PREVENT_TIMEOUT}" != "1"]','Set', 'PREVENT_TIMEOUT=0'));
                		$ext->add($c, 's', '', new ext_execif('$["${LOOPCOUNTER}" > "1"]', 'ExecIf', '$["${PREVENT_TIMEOUT}" = "0"]?SpeechBackground(${TIMEOUT_RETRY_REC},${TIMEOUT}):SpeechBackground(${INVALID_RETRY_REC},${TIMEOUT})'));
                		$ext->add($c, 's', '', new ext_setvar('PREVENT_TIMEOUT', 0));

				$ext->add($c, 's', '', new ext_execif('$[${REGEX("^[0-9]+$" ${SPEECH_TEXT(0)})}]', 'set', 'keypress=${SPEECH_TEXT(0)}'));
				//use dialplan_exist to verify we have dialplan there, starting with the dialcontext and moving locally in this context
				if (isset($directDialContext)) {
					$ext->add($c, 's', '', new ext_gotoif('$["${REGEX("^[0-9]+$" ${SPEECH_TEXT(0)})}" && ${DIALPLAN_EXISTS('.$directDialContext.',${keypress},1)}]?'.$directDialContext.',${keypress},1'));
				}
				$ext->add($c, 's', '', new ext_gotoif('$["${REGEX("^[0-9]+$" ${SPEECH_TEXT(0)})}" && ${DIALPLAN_EXISTS(ivrpro-'.$ivr['id'].',${keypress},1)}]', '${keypress},1'));//jump to dmtf ivr if dmtf was received
				$ext->add($c, 's', '', new ext_gotoif('$["${REGEX("^[0-9]+$" ${SPEECH_TEXT(0)})}" = "1"]','i,1'));
				$ext->add($c, 's', '', new ext_gotoif('$[${SPEECH(spoke)} = 0]', 'start'));//restart if we didnt get anything back

                		$ext->add($c, 's', '', new ext_noop('Number of speech results was: ${SPEECH(results)}'));
                		$ext->add($c, 's', '', new ext_execif('$["${SPEECH(results)}" > "0"]', 'Set', 'PREVENT_TIMEOUT=1'));
                		$ext->add($c, 's', '', new ext_execif('$["${SPEECH(results)}" > "0"]', 'Set', 'INVALID_COUNT=$[${INVALID_COUNT}+1]'));
				$ext->add($c, 's', '', new ext_setvar('RES_LOOP', '$[${SPEECH(results)}-1]'));
				$ext->add($c, 's', '', new ext_setvar('SPEECH_RES_COUNT', '${SPEECH(results)}'));


				//get speech result and add them in to variables, looping through the results
				$ext->add($c, 's', 'save-res', new ext_setvar('SPEECH_RES_TEXT_${RES_LOOP}', '${SPEECH_TEXT(${RES_LOOP})}'));
				$ext->add($c, 's', '', new ext_setvar('SPEECH_RES_SCORE_${RES_LOOP}', '${SPEECH_SCORE(${RES_LOOP})}'));
				$ext->add($c, 's', '', new ext_setvar('RES_LOOP', '$[${RES_LOOP}-1]'));
				$ext->add($c, 's', '', new ext_gotoif('$[${RES_LOOP} > -1]', 'save-res'));
				$ext->add($c, 's', '', new ext_setvar('RES_LOOP', '$[${SPEECH_RES_COUNT}-1]'));
				$ext->add($c, 's', '', new ext_speechdeactivategrammar('ivr' . $ivr['id']));
				$ext->add($c, 's', '', new ext_speechdestroy(''));

				//pick the first of our saved results and prosses it
				$ext->add($c, 's', 'res-loop', new ext_noop('Speech Recognition Score for ${RES_LOOP} was ${SPEECH_RES_SCORE_${RES_LOOP}}'));
				$ext->add($c, 's', '', new ext_noop('Speech Text for ${RES_LOOP} was ${SPEECH_RES_TEXT_${RES_LOOP}}'));
				//set variables for current speech entry
				$ext->add($c, 's', '', new ext_setvar('NAME', '${CUT(SPEECH_RES_TEXT_${RES_LOOP},_,2)}'));
				$ext->add($c, 's', '', new ext_setvar('AUDIO_TYPE', '${CUT(SPEECH_RES_TEXT_${RES_LOOP},_,4)}'));
				$ext->add($c, 's', '', new ext_setvar('DESTINATION', '${CUT(SPEECH_RES_TEXT_${RES_LOOP},_,3)}'));
				$ext->add($c, 's', '', new ext_setvar('RECORDING', '${CUT(SPEECH_RES_TEXT_${RES_LOOP},_,5)}'));
				$ext->add($c, 's', '', new ext_setvar('RES_LOOP', '$[${RES_LOOP}-1]'));
				$ext->add($c, 's', '', new ext_setvar('questionable_loop_${RES_LOOP}', ''));//need to clear this variable between speech loops
				$ext->add($c, 's', '', new ext_gotoif('$[${SPEECH_RES_COUNT} > 1]', 'questionable', 'score-based'));
				$ext->add($c, 's', '', new ext_gotoif('$[${RES_LOOP} = -1]', 'start'));//restart if weve exausted all our options

				//if we only have one entry, jump based on score.
				$ext->add($c, 's', 'score-based', new ext_gotoif('$[$[ "${SPEECH_RES_SCORE_0}" > "600"] && $[ "${SPEECH_RES_SCORE_0}" < "800"]]', 'questionable'));
				$ext->add($c, 's', '', new ext_gotoif('$[$[ "${SPEECH_RES_SCORE_0}" < "600"] && $[${RES_LOOP} > -1]]', 'res-loop'));
				$ext->add($c, 's', '', new ext_gotoif('$[$[ "${SPEECH_RES_SCORE_0}" < "600"] && $[${RES_LOOP} = -1]]', 'start'));

				//800 >;
				$ext->add($c, 's', 'GO', new ext_execif('$["${SPEECH(status)}" = "1"]', 'SpeechDestroy'));
				$ext->add($c, 's', '', new ext_execif('$["${ANNOUNCE_EXTEN}" = "1"]', 'Playback', 'pbx-transfer&to-extension'));
				$ext->add($c, 's', '', new ext_setvar('PLAYBACK_MODE', 'P'));//P=playback, no listen
				$ext->add($c, 's', '', new ext_execif('$["${ANNOUNCE_EXTEN}" = "1"]', 'Macro', 'ivrpro-play-tag'));
				$ext->add($c, 's', '', new ext_goto('${CUT(DESTINATION,^,3)}', '${CUT(DESTINATION,^,2)}', '${CUT(DESTINATION,^,1)}'));

				//600 > && < 800
				$ext->add($c, 's', 'questionable', new ext_noop('questionable_loop_${RES_LOOP} = ${questionable_loop_${RES_LOOP}}'));
				$ext->add($c, 's', '', new ext_execif('$["${SPEECH(status)}" = "1"]', 'SpeechDestroy'));
				$ext->add($c, 's', '', new ext_speechcreate(''));
				$ext->add($c, 's', '', new ext_gosubif('$["${ERROR}" = "1"]', 'no-ports,1'));
				$ext->add($c, 's', '', new ext_setvar('SPEECH_DTMF_MAXLEN', '1'));
				$ext->add($c, 's', '', new ext_setvar('questionable_loop_${RES_LOOP}', '${IF( $["${questionable_loop_${RES_LOOP}}" = ""]?1:${questionable_loop_${RES_LOOP}}+1)}'));
				$ext->add($c, 's', '', new ext_speechloadgrammar('yesno', '/etc/asterisk/ivrpro/yesno.gram'));
				$ext->add($c, 's', '', new ext_speechactivategrammar('yesno'));
				$ext->add($c, 's', '', new ext_execif('$["${questionable_loop_${RES_LOOP}}" > "1"]', 'SpeechBackground', '${INVALID_RETRY_REC},1'));
				$ext->add($c, 's', '', new ext_gotoif('$[${SPEECH(spoke)} = 1]', 'q-pros'));
				$ext->add($c, 's', '', new ext_execif('$["${questionable_loop_${RES_LOOP}}" = "${INVALID_RETRY}"]', 'SpeechBackground', '${INVALID_RETRY_REC},1'));
				$ext->add($c, 's', '', new ext_speechbackground('did-you-say', '${TIMEOUT}'));
				$ext->add($c, 's', '', new ext_gotoif('$[${SPEECH(spoke)} = 1]', 'q-pros'));
                		$ext->add($c, 's', '', new ext_macro('ivrpro-play-tag'));//used to confirm
                		$ext->add($c, 's', '', new ext_setvar('PREVENT_TIMEOUT', 1));
				$ext->add($c, 's', 'q-pros', new ext_speechdeactivategrammar('yesno'));
				$ext->add($c, 's', '', new ext_noop('{SPEECH_TEXT(0)} = ${SPEECH_TEXT(0)}, {RES_LOOP} = ${RES_LOOP}, {SPEECH(spoke)} = ${SPEECH(spoke)}'));
				$ext->add($c, 's', '', new ext_gotoif('$[$["${SPEECH(spoke)}" = "0"] && $["${questionable_loop_${RES_LOOP}}" < "${INVALID_RETRY}"] && !$[${REGEX("^[0-9]+$" ${SPEECH_TEXT(0)})}]]', 'questionable'));
				$ext->add($c, 's', '', new ext_gotoif('$[$["${SPEECH_TEXT(0)}" = "yes"] || $["${SPEECH_TEXT(0)}" = "1"]]', 'GO'));
				$ext->add($c, 's', '', new ext_gotoif('$[ $[$["${SPEECH_TEXT(0)}" = "no"] || $["${SPEECH_TEXT(0)}" = "2"]] && $[${RES_LOOP} > -1] ]', 'res-loop', 'start'));


				//draw a list of ivrs included by any queues
				$queues = queues_list(true);
				$qivr = array();
				foreach ($queues as $q) {
					$thisq = queues_get($q[0]);
					if ($thisq['context'] && strpos($thisq['context'], 'ivrpro-') === 0) {
						$qivr[] = str_replace('ivr-', '', $thisq['context']);
					}
				}


				//get our ivr entries
				$edest = ivr_get_entries($ivr['id']);
				$sorted_entries = array();
				foreach($edest as $selection) {
					$sorted_entries[] = array ('selection' => $selection['selection'], 'pro_retivr' => $ivr['pro_retivr'], 'dest' => $selection['dest']);
				}

				if ($sorted_entries) {
					foreach($sorted_entries as $pe) {
	     					//dont set a t or i if there already defined above
						if ($pe['selection'] == 't' && $ivr['pro_timeout_repeat_loops'] != 'disabled') {
							continue;
						}
						if ($pe['selection'] == 'i' && $ivr['pro_invalid_repeat_loops'] != 'disabled') {
							continue;
						}

						//only display these two lines if the ivr is included in any queues
						if (in_array($ivr['id'], $qivr)) {
							$ext->add($c, $pe['selection'],'', new ext_macro('blkvm-clr'));
							$ext->add($c, $pe['selection'], '', new ext_setvar('__NODEST', ''));

						}

						if ($ivr['pro_retivr']) {
							$ext->add($c, $pe['selection'], '',
								new ext_gotoif('$["x${IVR_CONTEXT_${CONTEXT}}" = "x"]',
								$pe['dest'] . ':${IVR_CONTEXT_${CONTEXT}},return,1'));
						} else {
							$ext->add($c, $pe['selection'],'', new ext_goto($pe['dest']));
						}
					}
				}

				//add our invalid and timeout checks here
				$ext->add($c, 'i', '', new ext_setvar('PREVENT_TIMEOUT',1));
				$ext->add($c, 'i', '', new ext_gotoif('$["${LOOPCOUNT}" >= "${INVALID_RETRY}"]', 'invalid', 's,start'));
				//$ext->add($c, 'i', '', new ext_execif('$[${LOOPCOUNT} = 1', 'Playback', '${INVALID_REC}', 'Playback','${INVALID_RETRY_REC}'));
				$ext->add($c, 't', '', new ext_gotoif('$["${LOOPCOUNT}" >= "${TIMEOUT_RETRY}-${INVALID_COUNT}"]', 'timeout', 's,start'));
				//$ext->add($c, 't', '', new ext_execif('$[${LOOPCOUNT} = 1','Playback','${TIMEOUT_REC}','Playback','${TIMEOUT_RETRY_REC}'));

				//loop here when there are no speech ports available
				$ext->add($c, 'no-ports', '', new ext_playback('one-moment-please'));
				$ext->add($c, 'no-ports', '', new ext_wait('3'));
				$ext->add($c, 'no-ports', '', new ext_setvar('NO_PORTS', '${IF( $["${NO_PORTS}" = ""] ? 1 : $[${NO_PORTS} + 1] )}'));
				$ext->add($c, 'no-ports', '', new ext_execif('$["${NO_PORTS}" > "1"]', 'goto,exit,exit'));
				$ext->add($c, 'no-ports', '', new ext_return());

				//exit from invalid here
				$ext->add($c, 'exit', '', new ext_noop('Oops, too many failures! Exiting now'));
				$ext->add($c, 'exit', '', new ext_playback('sorry-youre-having-problems'));
				$ext->add($c, 'exit', 'exit', new ext_goto('${CUT(INVALID_DEST,^,3)}', '${CUT(INVALID_DEST,^,2)}', '${CUT(INVALID_DEST,^,1)}'));

				//exit from invalid here
				$ext->add($c, 'invalid', '', new ext_noop('Oops, too many invalid destinations! Exiting now'));
				$ext->add($c, 'invalid', '', new ext_playback('${INVALID_REC}'));
				$ext->add($c, 'invalid', 'exit', new ext_goto('${CUT(INVALID_DEST,^,3)}', '${CUT(INVALID_DEST,^,2)}', '${CUT(INVALID_DEST,^,1)}'));

				//exit for timeout here
				$ext->add($c, 'timeout', '', new ext_noop('Oops, too many timeouts! Exiting now'));
				$ext->add($c, 'timeout', '', new ext_playback('${TIMEOUT_REC}'));
				$ext->add($c, 'timeout', 'exit', new ext_goto('${CUT(TIMEOUT_DEST,^,3)}', '${CUT(TIMEOUT_DEST,^,2)}', '${CUT(TIMEOUT_DEST,^,1)}'));

				//add macro that plays back the name tag
				$c = 'macro-ivrpro-play-tag';
				$if_speech_end = new ext_gotoif('$["${SPEECH(spoke)}" = "1"]', 'end,1');
				$ext->add($c, 's', '', new ext_setvar('AUDIO_TYPE', 'tts'));
				$ext->add($c, 's', '', new ext_noop('Playing tag of type ${AUDIO_TYPE} for ${EXTN}'));
				$ext->add($c, 's', '', new ext_execif('$[${ISNULL(${AUDIO_TYPE})}]', 'hangup'));
				$ext->add($c, 's', '', new ext_execif('$[${"${AUDIO_TYPE}" = ""}]', 'hangup'));
				$ext->add($c, 's', '', new ext_setvar('PLAY_APP', '${IF($["${PLAYBACK_MODE}" = "P"]?Playback:SpeechBackground)}'));
				$ext->add($c, 's', '', new ext_goto('1','${AUDIO_TYPE}'));

				//spell out the mathces name, by iterating over the name string
                                $ext->add($c, 'spell', '', new ext_noop('hit spell'));
                                $ext->add($c, 'spell', '', new ext_setvar('NAME_LEN', '${LEN(${NAME})}'));
                                $ext->add($c, 'spell', '', new ext_setvar('CUR_POS', '-1'));
                                $ext->add($c, 'spell', 'loop_top', new ext_setvar('CUR_POS', '$[${CUR_POS} + 1]'));
                                $ext->add($c, 'spell', '', new ext_noop('READING: ${NAME:${CUR_POS}:1}'));
                                $ext->add($c, 'spell', '', new ext_execif('$[${REGEX("[a-zA-Z]" ${NAME:${CUR_POS}:1})}]', '${PLAY_APP}', 'letters/${NAME:${CUR_POS}:1},1'));
                                $ext->add($c, 'spell', '', $if_speech_end);
                                $ext->add($c, 'spell', '', new ext_execif('$[${REGEX("[0-9]" ${NAME:${CUR_POS}:1})}]', '${PLAY_APP}', 'digits/${NAME:${CUR_POS}:1},1'));
                                $ext->add($c, 'spell', '', $if_speech_end);
                                $ext->add($c, 'spell', '', new ext_execif('$[${REGEX(" " ${NAME:${CUR_POS}:1})}]', '${PLAY_APP}', 'silence/1,1'));
                                $ext->add($c, 'spell', '', $if_speech_end);
                                $ext->add($c, 'spell', '', new ext_execif('$[${CUR_POS} != $[${NAME_LEN} - 1]]', 'Goto', 'loop_top'));//loop if htere are more charachters
                                $ext->add($c, 'spell', '', new ext_goto('end,1'));

				//we dont do tts at this time (yet?)
				$ext->add($c, 'tts', '', new ext_noop('hit tts'));
				$ext->add($c, 'tts', '', new ext_execif('$["${PLAYBACK_MODE}" = "P"]', 'flite','${NAME}'));
				$ext->add($c, 'tts', '', new ext_execif('$["${PLAYBACK_MODE}" = "P"]', 'macroexit'));
				$ext->add($c, 'tts', '', new ext_set('TMP_FLITE', '${ASTSPOOLDIR}/tmp/dirpro-tts-${EPOCH}${RAND(100,999)}'));
				$ext->add($c, 'tts', '', new ext_system('flite -t "${NAME}" -o ${TMP_FLITE}.wav'));
				$ext->add($c, 'tts', '', new ext_speechbackground('${TMP_FLITE}', ${TIMEOUT}));
				$ext->add($c, 'tts', '', new ext_system('rm ${TMP_FLITE}.wav &'));
				$ext->add($c, 'tts', '', new ext_execif('$[${ISNULL(${SPEECH(spoke)})}]', 'SpeechBackground', 'silence/1,3'));
				$ext->add($c, 'tts', '', new ext_macroexit());

				//playback a system recording
				$ext->add($c, 'sysrec', '', new ext_noop('hit sysrec'));
				$ext->add($c, 'sysrec', '', new ext_speechbackground('${RECORDING}', ${TIMEOUT}));
				$ext->add($c, 'sysrec', '', new ext_goto('end,1'));

				//return to ivr from voicemail
				if (isset($ivr['pro_retvm']) && $ivr['pro_retvm']) {
					// these need to be reset or inheritance problems makes them go away in some conditions
					//and infinite inheritance creates other problems
					$ext->add($c, 'return', '', new ext_setvar('_IVR_CONTEXT', '${CONTEXT}'));
					$ext->add($c, 'return', '', new ext_setvar('_IVR_CONTEXT_${CONTEXT}', '${IVR_CONTEXT_${CONTEXT}}'));
					$ext->add($c, 'return', '', new ext_goto('s,start'));
				}

				//end here
				$ext->add($c, 'end', '', new ext_execif('$[${SPEECH(spoke)} = 0]', 'SpeechBackground', 'silence/1,3'));
				$ext->add($c, 'end', '', new ext_macroexit());

				//this is the actually context where ivr pro starts, setting options for the rest of the session
				$c = 'ext-ivrpro';
				$ivr['pro_announcement'] = isset($ivr['pro_announcement']) && $ivr['pro_announcement'] ? recordings_get_file($ivr['pro_announcement']) : 'speech-dir-intro';
				$ivr['pro_invalid_recording'] = isset($ivr['pro_invalid_recording']) && $ivr['pro_invalid_recording'] != 0
												? recordings_get_file($ivr['pro_invalid_recording']) : 'no-valid-responce-pls-try-again';
				$ivr['pro_invalid_repeat_recording'] = isset($ivr['pro_invalid_repeat_recording']) && $ivr['pro_invalid_repeat_recording'] != 0
                                                                                                ? recordings_get_file($ivr['pro_invalid_repeat_recording']) : 'no-valid-responce-pls-try-again';
				$ivr['pro_timeout_recording'] = isset($ivr['pro_timeout_recording']) && $ivr['pro_timeout_recording'] != 0
                                                                                                ? recordings_get_file($ivr['pro_timeout_recording']) : 'no-valid-responce-pls-try-again';
				$ivr['pro_timeout_repeat_recording'] = isset($ivr['pro_timeout_repeat_recording']) && $ivr['pro_timeout_repeat_recording'] != 0
                                                                                                ? recordings_get_file($ivr['pro_timeout_repeat_recording']) : 'no-valid-responce-pls-try-again';

				$ext->add($c, $ivr['id'], '', new ext_noop('ivrpro speech ${EXTEN}'));
				$ext->add($c, $ivr['id'], '', new ext_setvar('GRAMFILE', '${EXTEN}'));
				$ext->add($c, $ivr['id'], '', new ext_setvar('WELCOM_REC', $ivr['pro_announcement']));
				$ext->add($c, $ivr['id'], '', new ext_setvar('TIMEOUT', $ivr['pro_timeout_time']));
				if($ivr['pro_invalid_repeat_loops'] != 'disabled' && $ivr['pro_invalid_repeat_loops'] > 0) {
					$ext->add($c, $ivr['id'], '', new ext_setvar('INVALID_RETRY', $ivr['pro_invalid_repeat_loops']));
					$ext->add($c, $ivr['id'], '', new ext_setvar('INVALID_RETRY_REC', $ivr['pro_invalid_repeat_recording']));
					$ext->add($c, $ivr['id'], '', new ext_setvar('INVALID_REC', $ivr['pro_invalid_recording']));
					$ext->add($c, $ivr['id'], '', new ext_setvar('INVALID_DEST', str_replace(',','^',$ivr['pro_invalid_destination'])));
				}
				if($ivr['pro_timeout_repeat_loops'] != 'disabled' && $ivr['pro_timeout_repeat_loops'] > 0) {
					$ext->add($c, $ivr['id'], '', new ext_setvar('TIMEOUT_RETRY', $ivr['pro_timeout_repeat_loops']));
					$ext->add($c, $ivr['id'], '', new ext_setvar('TIMEOUT_RETRY_REC', $ivr['pro_timeout_repeat_recording']));
					$ext->add($c, $ivr['id'], '', new ext_setvar('TIMEOUT_REC', $ivr['pro_timeout_recording']));
					$ext->add($c, $ivr['id'], '', new ext_setvar('TIMEOUT_DEST', str_replace(',','^',$ivr['pro_timeout_destination'])));
				}
				$ivr['pro_say_extension'] = isset($ivr['pro_say_extension']) && $ivr['pro_say_extension'] ? $ivr['pro_say_extension'] : '';
				$ext->add($c, $ivr['id'], '', new ext_setvar('ANNOUNCE_EXTEN', $ivr['pro_say_extension']));
				$ext->add($c, 's', '', new ext_setvar('_IVR_CONTEXT_${CONTEXT}=${IVR_CONTEXT}'));
				$ext->add($c, 's', '', new ext_setvar('_IVR_CONTEXT=${CONTEXT}'));
				if (isset($ivr['pro_retvm']) && $ivr['pro_retvm']) {
					$ext->add($c, 's', '', new ext_setvar('__IVR_RETVM', 'RETURN'));
				} else {
					//TODO: do we need to set anything at all?
					$ext->add($c, 's', '', new ext_setvar('__IVR_RETVM', ''));
				}
				$ext->add($c, $ivr['id'], '', new ext_goto('1','s','ivrpro-' .$ivr['id']));

				//write out gramamrs
				ivrpro_write_grammars();
			}
			if (!empty($directdial_contexts)) {
                                foreach($directdial_contexts as $dir_id) {
                                        $c = 'from-ivr-directory-' . $dir_id;
                                        $entries = function_exists('directory_get_dir_entries') ? directory_get_dir_entries($dir_id) : array();
                                        foreach ($entries as $dstring) {
                                                $exten = $dstring['dial'] == '' ? $dstring['foreign_id'] : $dstring['dial'];
                                                if ($exten == '' || $exten == 'custom') {
                                                        continue;
                                                }
                                                $ext->add($c, $exten, '', new ext_macro('blkvm-clr'));
                                                $ext->add($c, $exten, '', new ext_setvar('__NODEST', ''));
                                                $ext->add($c, $exten, '', new ext_goto('1', $exten, 'from-internal'));
                                        }
                                }
                        }
			break;
	}

}

function ivrpro_list() {
	$sql		= 'SELECT id,name FROM ivr_details ORDER BY name';
	$results	= sql($sql,'getAll',DB_FETCHMODE_ASSOC);
	return $results;
}

//jucie, juuuuuucie details..
function ivrpro_get_dir_details($id) {
	global $db;
	$id		= $db->escapeSimple($id);
	$sql	= "SELECT * FROM ivrpro_details WHERE ID = $id";
	$row	= sql($sql,'getRow',DB_FETCHMODE_ASSOC);
	return $row;
}

function ivrpro_delete($id){
	global $db, $amp_conf;
	$id = $db->escapeSimple($id);
	sql("DELETE FROM ivrpro_details WHERE id = $id");
	sql("DELETE FROM ivrpro_entries WHERE id = $id");

	//delete grammar file if it exists
	$file = $amp_conf['ASTETCDIR'] . '/ivrpro/ivr-' . $id . '.gram';
	if (file_exists($file)) {
		unlink($file);
	}
}

function ivrpro_destinations(){
	global $db;

	//ensure speech is enabled before returning it as a destination
	$sql		= 'select ivr_details.id, ivr_details.name FROM ivr_details
					LEFT JOIN ivrpro_details on ivr_details.id = ivrpro_details.id
						WHERE ivrpro_details.speech_enabled = 1
					ORDER BY name';
	$results	= sql($sql,'getAll',DB_FETCHMODE_ASSOC);

	foreach($results as $row){
		$row['name']	= ($row['name'])?$row['name']:'IVR '.$row['id'] ;
		$extens[] 		= array('destination' => 'ext-ivrpro,' . $row['id'] . ',1',
								'description' => $row['name'] . ' (speech)',
								'category' => _('IVR'));
	}
	return isset($extens) ? $extens : null;
}


// TODO: clean this up passing in $vals with expected positions for insert is very error prone! -PL
function ivrpro_save_dir_details($vals){
	global $db, $amp_conf;
	//dbug('ivrpro_save_dir_details called with ', $vals);
	foreach($vals as $key => $value) {
		$vals[$key] = $db->escapeSimple($value);
	}

	$sql	= 'REPLACE INTO ivrpro_details (id, speech_enabled, pro_directdial, pro_timeout_time, pro_announcement,
				pro_invalid_repeat_loops, pro_invalid_repeat_recording,
				pro_invalid_recording, pro_invalid_destination, pro_timeout_repeat_loops,
				pro_timeout_repeat_recording, pro_timeout_recording, pro_timeout_destination,
				pro_retivr)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
	$foo	= $db->query($sql,$vals);
	if(DB::IsError($foo)) {
		die_freepbx(print_r($vals,true).' '.$foo->getDebugInfo());
	}
	return $vals['id'];
}

function ivrpro_save_dir_entries($id, $entries){
	global $db;
	$id 	= $db->escapeSimple($id);
	sql("DELETE FROM ivrpro_entries WHERE id = $id");
	if ($entries) {
		foreach($entries['grammar'] as $my => $e){
			$sql = 'INSERT INTO ivrpro_entries (id, e_id, grammar) VALUES (?, ? , ?)';
			$foo = $db->query($sql, array($id, $my, $e));
			if(DB::IsError($foo)) {
				die_freepbx(print_r($vals,true).' '.$foo->getDebugInfo());
			}
		}
	}
}

function ivrpro_get_ivr_entries($id, $e_id){
	global $db;
	$sql = 'SELECT * FROM ivrpro_entries WHERE id = ?';
	if (isset($e_id) && $e_id !== FALSE) {
		$sql .= 'AND e_id = ?';
		$sqlArray = array($id, $e_id);
	} else {
		$sqlArray = array($id);
	}
	$res = $db->getAll($sql, $sqlArray, DB_FETCHMODE_ASSOC);
	return $res ? $res[0] : '';
}

//draw actuall html that will apear in ivr table
function ivrpro_draw_entries_ivr($opts) {
	$ret = false;
		$tr		= ivrpro_get_ivr_entries($opts['id'], $opts['ext']);
		$ret 	= '<textarea  name="entries[grammar]['
				. $opts['ext']
				. ']" placeholder="speech grammars"/>'
                . ((isset($tr['grammar']) && $opts['ext'] != '') ? $tr['grammar'] : '')
				. '</textarea>';

	return  array($ret);
}

function ivrpro_get_grammar_count($ivr_id) {
	if (isset($ivr_id)) {
		$sql = 'select count(grammar) as available_grammars from ivrpro_entries where grammar != "" and id = '.$ivr_id;
		$results = sql($sql, 'getAll', DB_FETCHMODE_ASSOC);

		if (isset($results) && !empty($results)) {
			foreach ($results as $result) {
				if (isset($result['available_grammars'])) {
					return $result['available_grammars'];
				}
			}
		}
	}
	return 0;
}

function ivrpro_write_grammars() {
	global $amp_conf;
	//write out grammars. Perhaps we should use a seperate function for this???
	$gram = '';
	//query should fill in the blanks where using the defualt names/values from the extension
	$sql = 'SELECT ivrpro_entries.id, ivrpro_entries.e_id, ivrpro_entries.grammar, ivr_entries.dest
			FROM ivrpro_entries
			LEFT JOIN ivrpro_details ON ivrpro_entries.id = ivrpro_details.id AND ivrpro_details.speech_enabled = "1"
			LEFT JOIN ivr_entries ON ivr_entries.ivr_id = ivrpro_entries.id and ivr_entries.selection = ivrpro_entries.e_id';
	//		GROUP BY ivrpro_entries.id, ivrpro_entries.e_id';
	$results = sql($sql, 'getAll', DB_FETCHMODE_ASSOC);

	if (isset($results) && $results) {
		foreach ($results as $r) {
			//replace multipal entires with pipes, which = OR in grammars
			$srch = array("\r\n", "\n", "\r");
			$r['grammar'] = str_replace($srch, ' | ', trim($r['grammar']));

			$spokenGrammar = explode('|',$r['grammar']);

			$r['dest'] = str_replace(',', '^',trim($r['dest']));
			$exten = $r['e_id'].'_'.$spokenGrammar[0].'_'.$r['dest'];
			//Later use for playing a recording
			if (isset($r['type']) && is_numeric($r['type']) && function_exists('recordings_get')) {
				$rex = recordings_get_id($r['type']);
				$record = $rex['filename'];
			} else {
				$record = '';
			}

			$gram[$r['id']][] = '(' . $r['grammar'] . ') {out="'
                                                                . $exten
                                                               // . '_'
                                                               // . ($record ? '_' . $record : '')
                                                                . '";}';
		}
	}

	if ($gram) {
		foreach ($gram as $ivr => $g) {
			$write = '';
			$write .= '#ABNF 1.0;' . "\n";
			$write .= 'mode voice;' . "\n";
			$write .= 'language en-US;' . "\n";
			$write .= 'tag-format <semantics/1.0.2006>;' . "\n\n";
			$write .= 'root $ivr' . $ivr . ';' . "\n\n";
			$write .= '$ivr' . $ivr . ' = (' . "\n\n";
			$write .= "\t  " . trim(implode("\n\t| ", $g), "\n\t| ") . "\n";
			$write .= ');' . "\n";
			//dbug($amp_conf['ASTETCDIR'] . '/ivrpro/ivr-' . $dir . '.gram', $write);
			file_put_contents($amp_conf['ASTETCDIR'] . '/ivrpro/ivr-' . $ivr . '.gram', $write);
		}
	}

}

//----------------------------------------------------------------------------
// Dynamic Destination Registry and Recordings Registry Functions
function ivrpro_check_destinations($dest=true) {
        global $active_modules;

        $destlist = array();
        if (is_array($dest) && empty($dest)) {
                return $destlist;
        }
	$sql = "SELECT dest, b.name name, e_id selection, a.id id FROM ivrpro_details a
		INNER JOIN ivrpro_entries d ON a.id = d.id
		INNER JOIN ivr_details b ON a.id = b.id
		INNER JOIN ivr_entries c ON a.id = c.ivr_id AND d.e_id = c.selection ";
        if ($dest !== true) {
                $sql .= "WHERE dest in ('".implode("','",$dest)."')";
        }
        $sql .= "ORDER BY name";
        $results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

        foreach ($results as $result) {
                $thisdest = $result['dest'];
                $thisid   = $result['id'];
                $name = $result['name'] ? $result['name'] . ' (speech)' : 'IVR ' . $thisid . ' (speech)';
                $destlist[] = array(
                        'dest' => $thisdest,
                        'description' => sprintf(_("IVR: %s / Option: %s"),$name,$result['selection']),
                        'edit_url' => 'config.php?display=ivr&action=edit&id='.urlencode($thisid),
                );
        }
        return $destlist;
}

function ivrpro_getdest($exten) {
        return array("ext-ivrpro,$exten,1");
}

function ivrpro_getdestinfo($dest) {
        global $active_modules;

        if (substr(trim($dest),0,11) == 'ext-ivrpro,') {
                $exten = explode(',',$dest);
		$exten = $exten[1];

                $thisexten = ivrpro_get_dir_details($exten);
                if (empty($thisexten)) {
                        return array();
                } else {
                        return array('description' => sprintf(_("IVR: %s"), ($thisexten['name'] ? $thisexten['name'] . ' (speech)': $thisexten['id'] . ' (speech)')),
                                     'edit_url' => 'config.php?display=ivr&action=edit&id='.urlencode($exten),
                                                                  );
                }
        } else {
                return false;
        }
}
