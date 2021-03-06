<?php
//  Copyright (C) 2011 by GENYMOBILE & Arnaud Dupuis
//  adupuis@genymobile.com
//  http://www.genymobile.com
// 
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 3 of the License, or
//  (at your option) any later version.
// 
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
// 
//  You should have received a copy of the GNU General Public License
//  along with this program; if not, write to the
//  Free Software Foundation, Inc.,
//  59 Temple Place - Suite 330, Boston, MA  02111-1307, USA

// Variable to configure global behaviour


$geny_ptr = new GenyProjectTaskRelation();
$geny_tools = new GenyTools();
date_default_timezone_set('Europe/Paris');

?>
<div id="mainarea">
	<p class="mainarea_title">
		<img src="images/<?php echo $web_config->theme; ?>/conges_add.png"></img>
		<span class="conges_add">
			Poser des congés
		</span>
	</p>
	<p class="mainarea_content">
		<p class="mainarea_content_intro">
		Ce formulaire permet de faire vos demandes de congés.<br />
		</p>
		<script>
			jQuery(document).ready(function(){
				$("#formID").validationEngine('init');
				// binds form submission and fields to the validation engine
				$("#formID").validationEngine('attach');
			});
		</script>

		<form id="formID" action="loader.php?module=conges_validation" method="post">
			<input type="hidden" name="create_conges" value="true" />
			<p>
				<label for="assignement_id">Projet</label>
				<select name="assignement_id" id="assignement_id" class="chzn-select" data-placeholder="Choisissez un projet..." >
					<?php
						$geny_assignements = new GenyAssignement();
						foreach( $geny_assignements->getAssignementsListByProfileId( $profile->id ) as $assignement ){
							$p = new GenyProject( $assignement->project_id );
							if(  $p->type_id == 5 && $p->status_id != 2 && $p->status_id != 3  ){
								echo "<option value=\"$assignement->id\" title=\"$p->description\">$p->name</input></option>";
							}
						}
					?>
				</select>
			</p>
			<p>
				<label for="task_id">Type</label>
				<select name="task_id" id="task_id" class="chzn-select" data-placeholder="Choisissez d'abord un projet..." >
					<option val=""></option>
				</select>
				<script>
					function getTasks(){
						var project_id = $("#assignement_id").val();
						if( project_id > 0 ) {
							$.get('backend/api/get_project_tasks_list.php?assignement_id='+project_id, function(data){
								$('.tasks_options').remove();
								$("#task_id").append('<option value="" class="tasks_options"></option>');
								$.each(data, function(key, val) {
									$("#task_id").append('<option class="tasks_options" value="' + val[0] + '" title="' + val[2] + '">' + val[1] + '</option>');
								});
								$("#task_id").attr('data-placeholder','Choisissez un type...');
								$("#task_id").trigger("liszt:updated");
								$("span:contains('Choisissez d'abord un projet...')").text('Choisissez un type...');

							},'json');
						}
					}
					$("#assignement_id").change(getTasks);
					getTasks();
					$(function() {
					$( "#assignement_start_date" ).datepicker();
					$( "#assignement_start_date" ).datepicker('setDate', new Date());
					$( "#assignement_start_date" ).datepicker( "option", "showAnim", "slideDown" );
					$( "#assignement_start_date" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
					$( "#assignement_start_date" ).datepicker( "option", "dayNames", ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'] );
					$( "#assignement_start_date" ).datepicker( "option", "dayNamesShort", ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'] );
					$( "#assignement_start_date" ).datepicker( "option", "dayNamesMin", ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'] );
					$( "#assignement_start_date" ).datepicker( "option", "monthNames", ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Decembre'] );
					$( "#assignement_start_date" ).datepicker( "option", "firstDay", 1 );
					$( "#assignement_end_date" ).datepicker();
					$( "#assignement_end_date" ).datepicker('setDate', new Date());
					$( "#assignement_end_date" ).datepicker( "option", "showAnim", "slideDown" );
					$( "#assignement_end_date" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
					$( "#assignement_end_date" ).datepicker( "option", "dayNames", ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'] );
					$( "#assignement_end_date" ).datepicker( "option", "dayNamesShort", ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'] );
					$( "#assignement_end_date" ).datepicker( "option", "dayNamesMin", ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'] );
					$( "#assignement_end_date" ).datepicker( "option", "monthNames", ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Decembre'] );
					$( "#assignement_end_date" ).datepicker( "option", "firstDay", 1 );
					
					$( "#assignement_start_date" ).change( function(){ $( "#assignement_end_date" ).val( $( "#assignement_start_date" ).val() ) } );
					
				});
				</script>
			</p>
			<p>
				<label for="assignement_start_date">Date de début</label>
				<input name="assignement_start_date" id="assignement_start_date" type="text" class="validate[required,custom[date]] text-input" />
			</p>
			<p>
				<label for="assignement_end_date">Date de fin</label>
				<input name="assignement_end_date" id="assignement_end_date" type="text" class="validate[required,custom[date]] text-input" />
			</p>
			<p>
				<label for="assignement_load">Charge journalière</label>
				<select name="assignement_load" id="assignement_load" class="chzn-select">
					<option value="4">4 Heures (1/2 journée)</option>
					<option value="8" selected="selected">8 Heures (1 journée)</option>
					
				</select>
			</p>
			<p>
				<input type="submit" value="Faire la demande" /> ou <a href="loader.php?module=conges_list">annuler</a>
			</p>
		</form>
	</p>
</div>

