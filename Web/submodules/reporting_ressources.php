<?php
//  Copyright (C) 2012 by GENYMOBILE & Jean-Charles Leneveu
//  jcleneveu@genymobile.com
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

include_once 'backend/api/ajax_toolbox.php';

setlocale( LC_TIME, 'fr_FR.utf8', 'fra' ); 

// déclaration des variables globales
$reporting_data = array();
$last_predictions = array();
$geny_project = new GenyProject();
$geny_project_type = new GenyProjectType();
$geny_profile = new GenyProfile();
$geny_client = new GenyClient();
$geny_assignement = new GenyAssignement();
$activity_report_ressources = new GenyActivityReportRessources();
$geny_assignements = array();
$geny_profil_management = new GenyProfileManagementData();
$gritter_notifications = array();

// récupération des paramètres rentrés par l'utilisateur
$param_year = getParam( 'year', date( "Y" ) );
$param_month = getParam( 'month', date( "m" ) );

// si le mois et l'année donnés par l'utilisateur sont correctes, on initialise $month et $year avec les valeurs de l'utilisateur
if( is_numeric( $param_year ) && is_numeric( $param_month ) && strlen( $param_year ) == 4 && intval( $param_month ) > 0 && intval( $param_year ) > 0 && intval( $param_month ) <=12 ) {
	$month = intval( $param_month );
	$year = intval( $param_year );
	$nb_day_in_month = date( 'd', mktime( 0, 0, 0, intval( $param_month ) + 1, 0, intval( $param_year ) ) );
}
// sinon par défaut on met le mois et l'année courante
else {
	$month = intval( date( "m" ) );
	$year = intval( date( "Y" ) );
	$nb_day_in_month = date( 'd', mktime( 0, 0, 0, intval( date( "m" ) ) + 1, 0, intval( date( "Y" ) ) ) );
}

// on initialise le tableau de données par profil
foreach( $geny_profile->getAllProfiles() as $tmp_profile ) {

	// on charge les informations annexes associées au profil
	$geny_profil_management->loadProfileManagementDataByProfileId( $tmp_profile->id );
	$geny_assignements = $geny_assignement->getActiveAssignementsListByProfileId( $tmp_profile->id );
	
	// restriction de profil => on ne prend que les gens qui ont des projets en cours et qui sont disponibles
	if( $tmp_profile->is_active && $geny_profil_management->availability_date <= ( date( "Y-m-d", mktime( 0, 0, 0, $month, $nb_day_in_month, $year ) ) ) && sizeof( $geny_assignements ) > 0 ) {
		
		// on initialise le tableau contenant les données de reporting
		$reporting_data[$tmp_profile->id] = array();
		
		// si le tableau contenant l'identifiant du dernier projet prédit 
		// de cet utilisateur n'existe pas, on l'initialise
		if( !isset( $last_predictions[$tmp_profile->id] ) )
			$last_predictions[$tmp_profile->id] = -1;
		
		// on ajoute le profil à la liste des profils actifs
		$active_profile_ids[] = $tmp_profile->id;
		
		// on continue d'initialiser le tableau de reporting
		// (par demi-journées, puis par journées)
		// 0 = matin, 1 = aprem
		for( $half_day = 0; $half_day < 2; $half_day ++ ) {
		
			// initialisations du tableau du matin/aprem
			if( !isset( $reporting_data[$tmp_profile->id][$half_day] ) ) {
				$reporting_data[$tmp_profile->id][$half_day] = array();
			}
		
			// initialisation du tableau suivant les jours
			for( $day = 1; $day <= $nb_day_in_month; $day ++ ) {
				if( !isset( $reporting_data[$tmp_profile->id][$half_day][$day] ) )
					$reporting_data[$tmp_profile->id][$half_day][$day] = array();
				
				// ["majority_project_id"] contient l'id du projet ayant le plus grand nombre d'heures => il va déterminer la couleur de la case
				if( !isset( $reporting_data[$tmp_profile->id][$half_day][$day]["majority_project_id"] ) )
					$reporting_data[$tmp_profile->id][$half_day][$day]["majority_project_id"] = -1 ;
				// ["total_prediction"] est un booléen indiquant si la couleur de la case a été prédite ou si elle a été obtenue directement des cras
				if( !isset( $reporting_data[$tmp_profile->id][$half_day][$day]["total_prediction"] ) )
					$reporting_data[$tmp_profile->id][$half_day][$day]["total_prediction"] = false ;
				// ["cras"] est un tableau contenant les projets sur lesquels l'utilisateur a travaillé
				if( !isset( $reporting_data[$tmp_profile->id][$half_day][$day]["cras"] ) )
					$reporting_data[$tmp_profile->id][$half_day][$day]["cras"] = array() ;
			}
		}
	}
}

// on parcourt tous les profils actifs précédemment trouvés
foreach( $active_profile_ids as $tmp_profile_id ) {
	// et tous les jours du mois
	for( $day = 1; $day <= $nb_day_in_month; $day ++ ) {
	
		// on obtient la date au format YYYY-MM-DD
		$tmp_date = date( "Y-m-d", mktime( 0, 0, 0, $month, $day, $year ) );
		
		// on parcourt tous les cras déclarés correspondant à la date et au profil
		foreach( $activity_report_ressources->getActivityReportsRessourcesFromDateAndProfileId( $tmp_date, $tmp_profile_id ) as $tmp_ressources ) {
			
			// on récupère la charge
			$tmp_numeric_activity_load = intval( $tmp_ressources->activity_load );
			
			// pour chaque demi-journée
			for( $half_day = 0; $half_day < 2; $half_day ++ ) {
			
				// on cherche combien on a d'heures pour cette période pour cet utilisateur en mémoire
				$tmp_total_nb_h[$half_day] = 0;
				foreach( $reporting_data[$tmp_profile_id][$half_day][$day]["cras"] as $cra ) {
					$tmp_total_nb_h[$half_day] += $cra["nb_h"];
				}
				
				// si la période n'est pas remplie et que la charge n'est pas encore nulle, on ajoute un nouveau cra
				if( $tmp_total_nb_h[$half_day] < 4 && $tmp_numeric_activity_load > 0 ) {
					// si jamais la charge du cra en cours rentre ENTIEREMENT dans la période
					if( $tmp_total_nb_h[$half_day] + $tmp_numeric_activity_load < 4 ) {
						$reporting_data[$tmp_profile_id][$half_day][$day]["cras"][] = array( "project_id" => $tmp_ressources->project_id ,
														"nb_h" => $tmp_numeric_activity_load ,
														"client_name" => $tmp_ressources->client_name ,
														"project_name" => $tmp_ressources->project_name ,
														"project_type_id" => $tmp_ressources->project_type_id ,
														"predicted" => false ) ;
						$tmp_total_nb_h[$half_day] += $tmp_numeric_activity_load;
						$tmp_numeric_activity_load = 0;
					}
					else {
						$reporting_data[$tmp_profile_id][$half_day][$day]["cras"][] = array( "project_id" => $tmp_ressources->project_id ,
														"nb_h" => 4 - $tmp_total_nb_h[$half_day] ,
														"client_name" => $tmp_ressources->client_name ,
														"project_name" => $tmp_ressources->project_name ,
														"project_type_id" => $tmp_ressources->project_type_id ,
														"predicted" => false ) ;
						$tmp_numeric_activity_load -= 4 - $tmp_total_nb_h[$half_day] ;
						$tmp_total_nb_h[$half_day] = 4;

					}
				}
			}
			// éventuellement, si $tmp_numeric_activity_load != 0, on a des heures sup'
		}
			
		// on parcourt par demi-journée
		for( $half_day = 0; $half_day < 2; $half_day ++ ) {
		
			// on exclu la prédiction pour le week-end : il est normal de ne pas avoir de cras pour le week-end
			if( date( "N", mktime( 0, 0, 0, intval( $month ), intval( $day ), intval( $year ) ) ) != "6" && date( "N", mktime( 0, 0, 0, intval( $month ), intval( $day ), intval( $year ) ) ) != "7") {
				
				// on cherche combien on a d'heures pour cette période pour cet utilisateur en mémoire
				$tmp_total_nb_h[$half_day] = 0;
				foreach( $reporting_data[$tmp_profile_id][$half_day][$day]["cras"] as $cra ) {
					$tmp_total_nb_h[$half_day] += $cra["nb_h"];
				}
				
				// si les cras ne sont pas complets, on prédit les cras manquants.
				if( $tmp_total_nb_h[$half_day] < 4 ) {
					// initialisation de l'id du projet prédit
					$predicted_project_id = -1;
					
					// on récupère les assignements associés au profil de l'utilisateur pour savoir à quels projets il est rattaché
					$geny_assignements = $geny_assignement->getActiveAssignementsListByProfileId( $tmp_profile_id );
					// cas n°1 : l'utilisateur n'est rattaché qu'à un seul projet => on prend celui-là
					if( sizeof( $geny_assignements ) == 1 ) {
						$predicted_project_id = $geny_assignements[0]->project_id;
					}
					// cas n°2 : si il n'y a pas de projets associés, l'id est négative
					elseif( sizeof( $geny_assignements ) == 0 ) {
						$predicted_project_id = -1 ;
					}
					// cas n°3 : (plus tordu) si l'utilisateur a plusieurs projets, on détermine le projet à considérer en fonction des prédictions précédentes
					else if( sizeof( $geny_assignements ) >= 2 ) {
						// si il n'y a pas de précédentes prédiction, on prend le premier projet de l'utilisateur
						if( $last_predictions[$tmp_profile_id] == -1 ) {
							$predicted_project_id = $geny_assignements[0]->project_id;
							$last_predictions[$tmp_profile_id] = $geny_assignements[0]->project_id;
						}
						// sinon, on cherche le projet qui suit la précédente prédiction
						else {
							// on cherche la prédiction précédente
							for( $tmp_cpt = 0; $tmp_cpt <= sizeof( $geny_assignements ); $tmp_cpt ++ ) {
								if( $last_predictions[$tmp_profile_id] == $geny_assignements[$tmp_cpt]->project_id ) {
									break;
								}
							}
							// on prend le projet suivant
							$tmp_cpt++;
							// si on a atteint la fin de la liste de projet, on prend le premier projet
							if( $tmp_cpt == sizeof( $geny_assignements ) ) {
								$tmp_cpt = 0;
							}
							$predicted_project_id = $geny_assignements[$tmp_cpt]->project_id;
							$last_predictions[$tmp_profile_id] = $geny_assignements[$tmp_cpt]->project_id;
						}
					}
					// cas n°4 : si on est dans aucun des cas précédents, l'id est négative par défaut
					else {
						$predicted_project_id = -1 ;
					}
					
					$geny_project->loadProjectById( $predicted_project_id );
					$geny_client->loadClientById( $geny_project->client_id );
					
					// ajout du cra "prédit"
					$reporting_data[$tmp_profile_id][$half_day][$day]["cras"][] = array( "project_id" => $predicted_project_id ,
														"nb_h" => 4 - $tmp_total_nb_h[$half_day] ,
														"client_name" => $geny_client->name ,
														"project_name" => $geny_project->name ,
														"project_type_id" => $geny_project->type_id ,
														"predicted" => true ) ;
				}
			}
			
			// initialisation du projet majoritaire en nb d'heures
			$majority_cra = array( "project_id" => -1,
				"nb_h" => 0,
				"predicted" => false );
			
			// on trouve le projet majoritaire en nb d'heures
			foreach( $reporting_data[$tmp_profile_id][$half_day][$day]["cras"] as $cra ) {
				if( $cra["nb_h"] > $majority_cra["nb_h"] ) {
					$majority_cra = $cra;
				}
			}
			
			// mise à jour de l'id de projet majoritaire en nb d'heures
			$reporting_data[$tmp_profile_id][$half_day][$day]["majority_project_id"] = $majority_cra["project_id"] ;
			
			// si le projet majoritaire a été prédit, on le précise lors de l'affichage
			if( $majority_cra["predicted"] == true ) {
				$reporting_data[$tmp_profile_id][$half_day][$day]["total_prediction"] = true;
			}
		}
	}
}

?>

<div id="mainarea">
	<p class="mainarea_title">
		<img src="images/<?php echo $web_config->theme; ?>/reporting_generic.png"></img>
		<span class="reporting_monthly_view">
			Tableau d'utilisation des ressources
		</span>
	</p>
	<p class="mainarea_content">
		<p class="mainarea_content_intro">
		Voici le tableau d'utilisation des ressources du mois sélectionné (par défaut le mois en cours).<br/>
		Reporting d'utilisation des ressources entre le <strong><?php echo date( "Y-m-d", mktime( 0, 0, 0, $month, 1, $year ) ); ?></strong> et le <strong><?php echo date( "Y-m-d", mktime( 0, 0, 0, $month, $nb_day_in_month, $year ) ); ?></strong>.<br/>
		</p>
		<style>
			@import 'styles/<?php echo $web_config->theme ?>/reporting_ressources_view.css';
		</style>
		<form id="select_ressources_date" action="loader.php?module=reporting_ressources" method="post">
			<p>
				<label for="month">Mois</label>
				<select name="month" id="month" type="text" class="chzn-select"/>
				<?php
					for( $tmp_month_id = 1; $tmp_month_id <= 12; $tmp_month_id ++ ) {
						$is_month_option_selected = "";
						if( intval( $tmp_month_id ) == intval( $month ) ) {
							echo $is_month_option_selected = " selected ";
						}
						echo '<option value="' . $tmp_month_id . '"' . $is_month_option_selected . '>' . strftime( "%B", mktime(1, 1, 1, $tmp_month_id, 1, 1 ) ) . '</option>';
					}
				?>
				</select>
			</p>
			<p>
				<label for="year">Année</label>
				<input name="year" id="year" type="text" value="<?php
				
					if( $param_year != "" && is_numeric($param_year) ) {
						echo $param_year;
					}
					else {
						echo date( "Y" );
					}
				?>"/>
			</p>
			<input type="submit" value="Ajuster le reporting" />
		</form>
		<div class="table_container">
		<p>
			
			<table id="reporting_load_table">
			<tr><th><div id="names">Nom Prénom</div></th>
			<?php
				for( $tmp_day_id = 1; $tmp_day_id <= $nb_day_in_month; $tmp_day_id ++ ) {
					echo '<th><div id="case">' . ( $tmp_day_id ) . "</div></th>";
				}
			?>
			</tr>
			<?php
				// on parcourt les données par profil
				foreach( $reporting_data as $tmp_profile_id => $tmp_period_data ) {
					
					// chargement du profil
					$geny_profile->loadProfileById( $tmp_profile_id );
					
					// affichage du nom
					$displayed_profile_name = substr( GenyTools::getProfileDisplayName( $geny_profile ), 0, 10 );
					if( $displayed_profile_name != GenyTools::getProfileDisplayName( $geny_profile ) ) {
						$displayed_profile_name = $displayed_profile_name . "...";
					}
					echo '<tr><th rowspan="2"><div id="names">' . $displayed_profile_name . '</div></th>';
					
					// on parcourt les données par période (matin/aprem)
					foreach( $tmp_period_data as $tmp_period => $tmp_days_data ) {
					
						// si c'est l'aprem on commence une nouvelle ligne (sinon la ligne a déjà été commencée par le nom du profil)
						if( $tmp_period == 1 ) {
							echo "<tr>";
						}
					
						// on parcourt les données par jour
						foreach( $tmp_days_data as $tmp_day => $tmp_data ) {
							
							$geny_project->loadProjectById( $tmp_data["majority_project_id"] );
								
							// on affiche une case colorée en fonction du type de projet si l'id > 0
							if( $tmp_data["majority_project_id"] > 0 ) {
							
								// on récupère la couleur associée au type de projet
								$project_type_background_color = $geny_project_type->getProjectTypeColor( $geny_project->type_id );
								
								$class_of_td = $geny_project->id;
								if( $tmp_data["total_prediction"] ) {
									$class_of_td = "predicted_td";
								}
								
								// on affiche la case
								echo '<td style="background-color:' . $project_type_background_color . '" class="' . $class_of_td . '">';
								echo '<a href="#" class="bulle"><div id="case">' . $tmp_data["majority_project_id"] . '</div><span>';
								
								// si la prédiction a détérminé la couleur de la case, on affiche qu'il s'agit d'une prédiction
								if( $tmp_data["total_prediction"] ) {
									echo '<div id="predicted-span-info">Prédiction</div>';
								}
								
								// on affiche les projets des cra déclarés par l'utilisateur + les prédictions éventuelles
								foreach( $tmp_data["cras"] as $cra ) {
									
									// récupération de la couleur du span en fonction de type de projet
									$project_type_background_color = $geny_project_type->getProjectTypeColor( $cra["project_type_id"] );
									
									// affichage du div
									echo '<div id="span-info" style="background-color:' . $project_type_background_color . '">' . $cra["client_name"] . " - " . $cra["project_name"] . " : " . $cra["nb_h"] . "h</div>";
								}
								
								echo '</span></a></td>';
							}
							//sinon si l'id < 0, on affiche une case grise
							else {
								echo '<td class="empty"><div id="case"></div></td>';
							}
						}
						echo "</tr>";
					}
				}
			?>
			</table>
		</p>
		</div>
	</p>
</div>
<?php
	$bottomdock_items = array( 'backend/widgets/reporting_cra_completion.dock.widget.php','backend/widgets/reporting_cra_status.dock.widget.php' );
?>