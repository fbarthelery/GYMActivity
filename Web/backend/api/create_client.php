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

session_start();
$required_group_rights = 2;
$auth_granted = false;

header('Content-Type: application/json;charset=UTF-8');

include_once 'ajax_authent_checking.php';
include_once 'ajax_toolbox.php';

try {
	$clients = array();
	if($auth_granted){
		$tmp_client = new GenyClient();
		$results = array();
		$label = getParam("label");
		
		if( $label != ""){
			if($tmp_client->insertNewClient(0,$label) > 0)
				echo json_encode(array("status" => "success", "status_message" => "Client créé avec succès." ));
			else
				echo json_encode(array("status" => "error", "status_message" => "Erreur durant la création du client." ));
		}
		else
			echo json_encode(array("status" => "error", "status_message" => "Un client doit obligatoirement avoir un nom." ));
	}
} catch (Exception $e) {
    echo "Exception: ".$e->getMessage(), "\n";
}

?>