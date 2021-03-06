<?php
/*
@name Radio Relay
@author Valentin CARRUESCO <idleman@idleman.fr>
@link http://blog.idleman.fr
@licence CC by nc sa
@version 1.0.0
@description Radio Relay plugin
*/

include('RadioRelay.class.php');




function radioRelay_plugin_setting_page(){
	global $_,$myUser,$conf;
	if(isset($_['section']) && $_['section']=='radioRelay' ){

		if($myUser!=false){
			$radioRelayManager = new RadioRelay();
			$radioRelays = $radioRelayManager->populate();
			$roomManager = new Room();
			$rooms = $roomManager->populate();

			//Si on est en mode modification
			if (isset($_['id'])){
				$id_mod = $_['id'];
				$selected = $radioRelayManager->getById($id_mod);
				$description = $selected->GetName();
				$button = "Modifier";
			}
			//Si on est en mode ajout
			else
			{
				$description =  "Ajout d'un relais";
				$button = "Ajouter";
			}
			?>

			<div class="span9 userBloc">


				<h1>Relais</h1>
				<p>Gestion des relais radio</p>  
				<form action="action.php?action=radioRelay_add_radioRelay" method="POST">
					<fieldset>
						<legend><?php  echo $description ?></legend>

						<div class="left">
							<label for="nameRadioRelay">Nom</label>
							<?php  if(isset($selected)){echo '<input type="hidden" name="id" value="'.$id_mod.'">';} ?>
							<input type="text" id="nameRadioRelay" value="<?php  if(isset($selected)){echo $selected->getName();} ?>" onkeyup="$('#vocalCommand').html($(this).val());" name="nameRadioRelay" placeholder="Lumiere Canapé…"/>
							<small>Commande vocale associée : "<?php echo $conf->get('VOCAL_ENTITY_NAME'); ?>, allume <span id="vocalCommand"></span>"</small>
							<label for="descriptionRadioRelay">Description</label>
							<input type="text" value="<?php if(isset($selected)){echo $selected->getDescription();} ?>" name="descriptionRadioRelay" id="descriptionRadioRelay" placeholder="Relais sous le canapé…" />
							<label for="radioCodeRadioRelay">Code radio</label>
							<input type="text" value="<?php if(isset($selected)){echo $selected->getRadioCode();} ?>" name="radioCodeRadioRelay" id="radioCodeRadioRelay" placeholder="0,1,2…" />
							<label for="roomRadioRelay">Pièce</label>
							<select name="roomRadioRelay" id="roomRadioRelay">
								<?php foreach($rooms as $room){ 
									if (isset($selected)){$selected_room = ($selected->getRoom());
									}else if(isset($_['room'])){
										$selected_room = $_['room'];
									}else{
										$selected_room = null;
									}			    		
									?>

									<option <?php  if ($selected_room == $room->getId()){echo "selected";} ?> value="<?php echo $room->getId(); ?>"><?php echo $room->getName(); ?></option>
									<?php } ?>
								</select>
							<label for="pulseRadioRelay">Mode impulsion (laisser à zéro pour désactiver le mode impulsion ou definir un temps d'impulsion en milli-seconde)</label>
							<input type="text" name="pulseRadioRelay" value="<?php if(isset($selected))echo $selected->getPulse(); else echo "0";?>" id="pulseWireRelay" placeholder="0" />
			    
							</div>

							<div class="clear"></div>
							<br/><button type="submit" class="btn"><?php  echo $button; ?></button>
						</fieldset>
						<br/>
					</form>

					<table class="table table-striped table-bordered table-hover">
						<thead>
							<tr>
								<th>Nom</th>
								<th>Description</th>
								<th>Code radio</th>
								<th>Pièce</th>
								<th>Impulsion</th>
								<th></th>
							</tr>
						</thead>

						<?php foreach($radioRelays as $radioRelay){ 

							$room = $roomManager->load(array('id'=>$radioRelay->getRoom())); 
							?>
							<tr>
								<td><?php echo $radioRelay->getName(); ?></td>
								<td><?php echo $radioRelay->getDescription(); ?></td>
								<td><?php echo $radioRelay->getRadioCode(); ?></td>
								<td><?php echo $room->getName(); ?></td>
								<td><?php echo $radioRelay->getPulse(); ?></td>
								<td><a class="btn" href="action.php?action=radioRelay_delete_radioRelay&id=<?php echo $radioRelay->getId(); ?>"><i class="fa fa-times"></i></a>
									<a class="btn" href="setting.php?section=radioRelay&id=<?php echo $radioRelay->getId(); ?>"><i class="fa fa-pencil"></i></a></td>
								</tr>
								<?php } ?>
							</table>
						</div>

						<?php }else{ ?>

						<div id="main" class="wrapper clearfix">
							<article>
								<h3>Vous devez être connecté</h3>
							</article>
						</div>
						<?php
					}
				}

			}

		function radioRelay_plugin_setting_menu(){
			global $_;
			echo '<li '.(isset($_['section']) && $_['section']=='radioRelay'?'class="active"':'').'><a href="setting.php?section=radioRelay"><i class="fa fa-angle-right"></i> Relais radio</a></li>';
		}


		function radioRelay_vocal_command(&$response,$actionUrl){
			global $conf;
			$radioRelayManager = new RadioRelay();

			$radioRelays = $radioRelayManager->populate();
			foreach($radioRelays as $radioRelay){
				$response['commands'][] = array('command'=>$conf->get('VOCAL_ENTITY_NAME').', allume '.$radioRelay->getName(),'url'=>$actionUrl.'?action=radioRelay_change_state&engine='.$radioRelay->getId().'&state=on&webservice=true','confidence'=>'0.9');
				$response['commands'][] = array('command'=>$conf->get('VOCAL_ENTITY_NAME').', eteint '.$radioRelay->getName(),'url'=>$actionUrl.'?action=radioRelay_change_state&engine='.$radioRelay->getId().'&state=off&webservice=true','confidence'=>'0.9');
			}
		}


		function dash_radioRelay_plugin_menu(&$widgets){
			$roomManager = new Room();
			$rooms = $roomManager->populate('name');
			foreach($rooms as $room){
				$widgets[] = array(
			    'uid'      => 'dash_radioRelay',
			    'icon'     => 'fa fa-lightbulb-o',
			    'label'    => 'Relai radio '.$room->getName(),
			    'data'     => array('room'=>$room->getId(),'roomName'=>$room->getName()),
			    'background' => '#182E7D', 
			    'color' => '#fffffff',
			    'onLoad'   => 'action.php?action=dash_radioRelay_plugin_load',
				);
			}
		}

		function radioRelay_action_radioRelay(){
			global $_,$conf,$myUser;

			//Mise à jour des droits
			$myUser->loadRight();

			switch($_['action']){


				case 'dash_radioRelay_plugin_load':
					header('Content-type: application/json');
					$response = array();
					

					$data = json_decode(html_entity_decode($_['widget']['data']),true);
					$response['title'] = 'Relais radio '.$data['roomName'];

					$response['content'] = '<div style="width: 100%">';
					if(fileperms(Plugin::path().'radioEmission')!='36333')
						$response['content'] .= '<div class="flatBloc pink-color">Attention, les droits vers le fichier <br/> radioEmission sont mal réglés.<br/> Référez vous à <span style="cursor:pointer;text-decoration:underline;" onclick="window.location.href=\'https://github.com/ldleman/yana-server#installation\';">la doc</span> pour les régler</div>';
					
					$response['content'] .= '<ul class="plugin_radiorelay_button">';
					$radioRelayManager = new RadioRelay();

					$radioRelays = $radioRelayManager->loadAll(array('room'=>$data['room']));
					foreach($radioRelays as $radioRelay){
						$response['content'] .='<li title="'.$radioRelay->getDescription()."\rCode :".$radioRelay->getRadioCode().'"><button  onclick="plugin_radiorelay_state('.$radioRelay->getId().',this);" class="btn plugin_radiorelay_button_switch '.($radioRelay->state?'btn-warning':'').'"><i class="fa fa-lightbulb-o"></i></button> '.$radioRelay->getName().'</li>';
					}
		
					$response['content'] .='</ul></div>';

					

					echo json_encode($response);
					exit(0);

				break;


				case 'radioRelay_change_state':
					Action::write(function($_,&$response){
						global $myUser,$conf;
						$radioRelay = new RadioRelay();
						$radioRelay = $radioRelay->getById($_['id']);

						if(!is_object($radioRelay)) throw new Exception("Relais introuvable en base de données");
						Event::emit('relay_change_state',array('relay'=>$radioRelay,'state'=>$_['state']));
						
						$cmd = dirname(__FILE__).'/radioEmission '.$conf->get('plugin_radioRelay_emitter_pin').' '.$conf->get('plugin_radioRelay_emitter_code').' '.$radioRelay->getRadioCode().' ';
						//Gestion du mode pulse ou du simple on/off
						$cmd .= $radioRelay->getPulse()==0 ? $_['state'] : 'pulse '.$radioRelay->getPulse();
					
						$radioRelay->state = $_['state'];
						Functions::log('Launch system command : '.$cmd);
						system($cmd,$out);
						$radioRelay->save();

						$response['responses']= 
										array(
											array(
												'type'=>'talk',
												'sentence'=>Personality::response('ORDER_CONFIRMATION')
											)
										)
									;
					},array('radio relais'=>'u'));

				break;


				case 'radioRelay_delete_radioRelay':
					if($myUser->can('radio relais','d')){
						$radioRelayManager = new RadioRelay();
						$radioRelayManager->delete(array('id'=>$_['id']));
						header('location:setting.php?section=radioRelay');
					}
					else
					{
						header('location:setting.php?section=radioRelay&error=Vous n\'avez pas le droit de faire ça!');
					}

				break;

				case 'radioRelay_plugin_setting':
					$conf->put('plugin_radioRelay_emitter_pin',$_['emiterPin']);
					$conf->put('plugin_radioRelay_emitter_code',$_['emiterCode']);
					header('location: setting.php?section=preference&block=radioRelay');
				break;

				case 'radioRelay_add_radioRelay':

					//Vérifie si on veut modifier ou ajouter un relai
					$right_toverify = isset($_['id']) ? 'u' : 'c';

					if($myUser->can('radio relais',$right_toverify)){
						$radioRelay = new RadioRelay();
						//Si modification on charge la ligne au lieu de la créer
						if ($right_toverify == "u"){$radioRelay = $radioRelay->load(array("id"=>$_['id']));}
						$radioRelay->setName($_['nameRadioRelay']);
						$radioRelay->setDescription($_['descriptionRadioRelay']);
						$radioRelay->setRadioCode($_['radioCodeRadioRelay']);
						$radioRelay->setRoom($_['roomRadioRelay']);
						$radioRelay->setPulse($_['pulseRadioRelay']);
						$radioRelay->save();
						header('location:setting.php?section=radioRelay');
					}
					else
					{
						header('location:setting.php?section=radioRelay&error=Vous n\'avez pas le droit de faire ça!');
					}


				break;

		
			}
		}


		function radioRelay_plugin_preference_menu(){
			global $_;
			echo '<li '.(@$_['block']=='radioRelay'?'class="active"':'').'><a  href="setting.php?section=preference&block=radioRelay"><i class="fa fa-angle-right"></i> Radio Relais</a></li>';
		}
		function radioRelay_plugin_preference_page(){
			global $myUser,$_,$conf;
			if((isset($_['section']) && $_['section']=='preference' && @$_['block']=='radioRelay' )  ){
				if($myUser!=false){
					?>

					<div class="span9 userBloc">
						<form class="form-inline" action="action.php?action=radioRelay_plugin_setting" method="POST">

							<p>Pin du raspberry PI branché à l'émetteur radio: </p>
							<input type="text" class="input-large" name="emiterPin" value="<?php echo $conf->get('plugin_radioRelay_emitter_pin');?>" placeholder="Pin wiring PI...">

							<p>Code de la télécommande pris par le raspberry pi: </p>
							<input type="text" class="input-large" name="emiterCode" value="<?php echo $conf->get('plugin_radioRelay_emitter_code');?>" placeholder="par exemple 8217034...">

							<button type="submit" class="btn">Enregistrer</button>
						</form>
					</div>

					<?php }else{ ?>

					<div id="main" class="wrapper clearfix">
						<article>
							<h3>Vous devez être connecté</h3>
						</article>
					</div>
					<?php

				}
			}
		}

		Plugin::addCss('/css/style.css',true);
		Plugin::addJs('/js/main.js',true);
		Plugin::addHook("preference_menu", "radioRelay_plugin_preference_menu"); 
		Plugin::addHook("preference_content", "radioRelay_plugin_preference_page"); 
		Plugin::addHook("action_post_case", "radioRelay_action_radioRelay"); 

		Plugin::addHook("setting_bloc", "radioRelay_plugin_setting_page");
		Plugin::addHook("setting_menu", "radioRelay_plugin_setting_menu");  
		Plugin::addHook("vocal_command", "radioRelay_vocal_command");
		Plugin::addHook("widgets", "dash_radioRelay_plugin_menu");
		//Anonnce que le plugin propose un évenement à l'application lors du changement d'etat (cf Event::emit('relay_change_state') dans le code )
		Event::announce('relay_change_state', 'Changement de l\'état d\'un relais radio',array('code radio'=>'int','etat'=>'string'));

		?>
