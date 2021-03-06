<?php
//  Copyright (C) 2011 by GENYMOBILE

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


include_once 'GenyWebConfig.php';
include_once 'GenyDatabaseTools.php';

class GenyIntranetPage extends GenyDatabaseTools {
	
	public $id = -1;
	public $title = '';
	public $intranet_category_id = -1;
	public $intranet_type_id = -1;
	public $status_id = -1;
	public $acl_modification_type = 'owner';
	public $profile_id = -1;
	public $description = '';
	public $content = '';
	
	public function __construct( $id = -1 ) {
		parent::__construct( "IntranetPages", "intranet_page_id" );
		$this->id = -1;
		$this->title = '';
		$this->intranet_category_id = -1;
		$this->intranet_type_id = -1;
		$this->status_id = -1;
		$this->acl_modification_type = 'owner';
		$this->profile_id = -1;
		$this->description = '';
		$this->content = '';
		if( $id > -1 ) {
			$this->loadIntranetPageById( $id );
		}
	}

	public function insertNewIntranetPage( $id, $intranet_page_title, $intranet_category_id, $intranet_type_id, $intranet_page_status_id, $intranet_page_acl_modification_type, $profile_id, $intranet_page_description, $intranet_page_content ) {
		$query = "INSERT INTO IntranetPages VALUES($id,'".mysql_real_escape_string( $intranet_page_title )."','".$intranet_category_id."','".$intranet_type_id."','".$intranet_page_status_id."','".mysql_real_escape_string( $intranet_page_acl_modification_type )."','".$profile_id."','".mysql_real_escape_string( $intranet_page_description )."','".mysql_real_escape_string( gzcompress( $intranet_page_content ) )."')";
		if( $this->config->debug ) {
			error_log( "[GYMActivity::DEBUG] GenyIntranetPage MySQL query : $query", 0 );
		}
		if( mysql_query( $query, $this->handle ) ) {
			return mysql_insert_id( $this->handle );
		}
		else {
			return -1;
		}
	}

	public function removeIntranetPage( $id = 0 ) {
		if( is_numeric( $id ) ) {
			if( $id == 0 && $this->id > 0 ) {
				$id = $this->id;
			}
			if( $id <= 0 ) {
				return -1;
			}
			$query = "DELETE FROM IntranetPages WHERE intranet_page_id=$id";
			if( $this->config->debug ) {
				error_log( "[GYMActivity::DEBUG] GenyIntranetPage MySQL DELETE query : $query", 0 );
			}
			if( mysql_query( $query,$this->handle ) ) {
				return 1;
			}
			else {
				return -1;
			}
		}
		return -1;
	}

	public function getIntranetPagesListWithRestrictions( $restrictions, $restriction_type = "AND" ) {
		// $restrictions is in the form of array("intranet_category_id=1","intranet_type_id=2")
		$last_index = count( $restrictions ) - 1;
		$query = "SELECT intranet_page_id,intranet_page_title,intranet_category_id,intranet_type_id,intranet_page_status_id,intranet_page_acl_modification_type,profile_id,intranet_page_description,intranet_page_content FROM IntranetPages";
		if( count( $restrictions ) > 0 ) {
			$query .= " WHERE ";
			$op = mysql_real_escape_string( $restriction_type );
			foreach( $restrictions as $key => $value ) {
				$query .= $value;
				if( $key != $last_index ){
					$query .= " $op ";
				}
			}
		}
		if( $this->config->debug ) {
			error_log( "[GYMActivity::DEBUG] GenyIntranetPage MySQL query : $query", 0 );
		}
		$result = mysql_query( $query, $this->handle );
		$intranet_pages_list = array();
		if( mysql_num_rows( $result ) != 0 ) {
			while( $row = mysql_fetch_row( $result ) ) {
				$tmp_intranet_page = new GenyIntranetPage();
				$tmp_intranet_page->id = $row[0];
				$tmp_intranet_page->title = $row[1];
				$tmp_intranet_page->intranet_category_id = $row[2];
				$tmp_intranet_page->intranet_type_id = $row[3];
				$tmp_intranet_page->status_id = $row[4];
				$tmp_intranet_page->acl_modification_type = $row[5];
				$tmp_intranet_page->profile_id = $row[6];
				$tmp_intranet_page->description = $row[7];
				$tmp_intranet_page->content = gzuncompress( $row[8] );
				$intranet_pages_list[] = $tmp_intranet_page;
			}
		}
// 		mysql_close();
		return $intranet_pages_list;
	}
	
	public function getAllIntranetPages(){
		return $this->getIntranetPagesListWithRestrictions( array() );
	}
	
	public function getIntranetPagesByType( $intranet_type_id ) {
		$intranet_pages = $this->getIntranetPagesListWithRestrictions( array( "intranet_type_id='".mysql_real_escape_string( $intranet_type_id )."'" ) );
		$intranet_pages_list = array();
		foreach( $intranet_pages as $intranet_page ) {
			$tmp_intranet_page = new GenyIntranetPage();
			$tmp_intranet_page->id = $intranet_page->id;
			$tmp_intranet_page->title = $intranet_page->title;
			$tmp_intranet_page->intranet_category_id = $intranet_page->intranet_category_id;
			$tmp_intranet_page->intranet_type_id = $intranet_page->intranet_type_id;
			$tmp_intranet_page->status_id = $intranet_page->status_id;
			$tmp_intranet_page->acl_modification_type = $intranet_page->acl_modification_type;
			$tmp_intranet_page->profile_id = $intranet_page->profile_id;
			$tmp_intranet_page->description = $intranet_page->description;
			$tmp_intranet_page->content = $intranet_page->content;
			$intranet_pages_list[] = $tmp_intranet_page;
		}
		return $intranet_pages_list;
	}
	
	public function getIntranetPagesByCategory( $intranet_category_id ) {
		$intranet_pages = $this->getIntranetPagesListWithRestrictions( array( "intranet_category_id='".mysql_real_escape_string( $intranet_category_id )."'" ) );
		$intranet_pages_list = array();
		foreach( $intranet_pages as $intranet_page ) {
			$tmp_intranet_page = new GenyIntranetPage();
			$tmp_intranet_page->id = $intranet_page->id;
			$tmp_intranet_page->title = $intranet_page->title;
			$tmp_intranet_page->intranet_category_id = $intranet_page->intranet_category_id;
			$tmp_intranet_page->intranet_type_id = $intranet_page->intranet_type_id;
			$tmp_intranet_page->status_id = $intranet_page->status_id;
			$tmp_intranet_page->acl_modification_type = $intranet_page->acl_modification_type;
			$tmp_intranet_page->profile_id = $intranet_page->profile_id;
			$tmp_intranet_page->description = $intranet_page->description;
			$tmp_intranet_page->content = $intranet_page->content;
			$intranet_pages_list[] = $tmp_intranet_page;
		}
		return $intranet_pages_list;
	}

	public function getIntranetPagesByTag( $intranet_tag_id ) {
		
		$query = "SELECT IntranetPages.intranet_page_id, intranet_page_title, intranet_category_id, intranet_type_id, intranet_page_status_id, intranet_page_acl_modification_type, profile_id, intranet_page_description, intranet_page_content FROM IntranetPages, IntranetTagPageRelations WHERE IntranetPages.intranet_page_id = IntranetTagPageRelations.intranet_page_id AND IntranetTagPageRelations.intranet_tag_id=".$intranet_tag_id;
		
		$result = mysql_query( $query, $this->handle );
		if( $this->config->debug ) {
			error_log( "[GYMActivity::DEBUG] GenyIntranetPage MySQL query : $query", 0 );
		}
		
		$intranet_pages_list = array();
		if( mysql_num_rows( $result ) != 0 ) {
			while( $row = mysql_fetch_row( $result ) ) {
				$tmp_intranet_page = new GenyIntranetPage();
				$tmp_intranet_page->id = $row[0];
				$tmp_intranet_page->title = $row[1];
				$tmp_intranet_page->intranet_category_id = $row[2];
				$tmp_intranet_page->intranet_type_id = $row[3];
				$tmp_intranet_page->status_id = $row[4];
				$tmp_intranet_page->acl_modification_type = $row[5];
				$tmp_intranet_page->profile_id = $row[6];
				$tmp_intranet_page->description = $row[7];
				$tmp_intranet_page->content = gzuncompress( $row[8] );
				$intranet_pages_list[] = $tmp_intranet_page;
			}
		}
		return $intranet_pages_list;
	}
	
	public function getIntranetPagesByStatus( $intranet_page_status_id ) {
		$intranet_pages = $this->getIntranetPagesListWithRestrictions( array( "intranet_page_status_id='".mysql_real_escape_string( $intranet_page_status_id )."'" ) );
		$intranet_pages_list = array();
		foreach( $intranet_pages as $intranet_page ) {
			$tmp_intranet_page = new GenyIntranetPage();
			$tmp_intranet_page->id = $intranet_page->id;
			$tmp_intranet_page->title = $intranet_page->title;
			$tmp_intranet_page->intranet_category_id = $intranet_page->intranet_category_id;
			$tmp_intranet_page->intranet_type_id = $intranet_page->intranet_type_id;
			$tmp_intranet_page->status_id = $intranet_page->status_id;
			$tmp_intranet_page->acl_modification_type = $intranet_page->acl_modification_type;
			$tmp_intranet_page->profile_id = $intranet_page->profile_id;
			$tmp_intranet_page->description = $intranet_page->description;
			$tmp_intranet_page->content = $intranet_page->content;
			$intranet_pages_list[] = $tmp_intranet_page;
		}
		return $intranet_pages_list;
	}
	
	public function getIntranetPagesByProfile( $profile_id ) {
		$intranet_pages = $this->getIntranetPagesListWithRestrictions( array( "profile_id='".mysql_real_escape_string( $profile_id )."'" ) );
		$intranet_pages_list = array();
		foreach( $intranet_pages as $intranet_page ) {
			$tmp_intranet_page = new GenyIntranetPage();
			$tmp_intranet_page->id = $intranet_page->id;
			$tmp_intranet_page->title = $intranet_page->title;
			$tmp_intranet_page->intranet_category_id = $intranet_page->intranet_category_id;
			$tmp_intranet_page->intranet_type_id = $intranet_page->intranet_type_id;
			$tmp_intranet_page->status_id = $intranet_page->status_id;
			$tmp_intranet_page->acl_modification_type = $intranet_page->acl_modification_type;
			$tmp_intranet_page->profile_id = $intranet_page->profile_id;
			$tmp_intranet_page->description = $intranet_page->description;
			$tmp_intranet_page->content = $intranet_page->content;
			$intranet_pages_list[] = $tmp_intranet_page;
		}
		return $intranet_pages_list;
	}
	
	public function searchIntranetPages( $term ) {
		$q = mysql_real_escape_string( $term );
		return $this->getIntranetPagesListWithRestrictions( array( "intranet_page_title LIKE '%$q%' or intranet_page_content '%$q%'" ) );
	}

	public function loadIntranetPageById( $id ) {
		$intranet_pages = $this->getIntranetPagesListWithRestrictions( array( "intranet_page_id=".mysql_real_escape_string( $id ) ) );
		$intranet_page = $intranet_pages[0];
		if( isset( $intranet_page ) && $intranet_page->id > -1 ) {
			$this->id = $intranet_page->id;
			$this->title = $intranet_page->title;
			$this->intranet_category_id = $intranet_page->intranet_category_id;
			$this->intranet_type_id = $intranet_page->intranet_type_id;
			$this->status_id = $intranet_page->status_id;
			$this->acl_modification_type = $intranet_page->acl_modification_type;
			$this->profile_id = $intranet_page->profile_id;
			$this->description = $intranet_page->description;
			$this->content = $intranet_page->content;
		}
	}
	
	public function loadIntranetPageByTitle( $intranet_page_title ) {
		$intranet_pages = $this->getIntranetPagesListWithRestrictions( array( "intranet_page_title='".mysql_real_escape_string( $intranet_page_title )."'" ) );
		if( count( $intranet_pages ) == 0 ) {
			return;
		}
		$intranet_page = $intranet_pages[0];
		if( isset( $intranet_page ) && $intranet_page->id > -1 ) {
			$this->id = $intranet_page->id;
			$this->title = $intranet_page->title;
			$this->intranet_category_id = $intranet_page->intranet_category_id;
			$this->intranet_type_id = $intranet_page->intranet_type_id;
			$this->status_id = $intranet_page->status_id;
			$this->acl_modification_type = $intranet_page->acl_modification_type;
			$this->profile_id = $intranet_page->profile_id;
			$this->description = $intranet_page->description;
			$this->content = $intranet_page->content;
		}
	}
	
	public function loadIntranetPageByHistoryId( $id ) {
		
		$query = "SELECT DISTINCT IntranetPages.intranet_page_id, intranet_page_title, intranet_category_id, intranet_type_id, IntranetPages.intranet_page_status_id, intranet_page_acl_modification_type, IntranetPages.profile_id, intranet_page_description, intranet_page_content FROM IntranetPages, IntranetHistories WHERE IntranetPages.intranet_page_id = IntranetHistories.intranet_page_id AND IntranetHistories.intranet_history_id=".$id;
				
		$result = mysql_query( $query, $this->handle );
		if( $this->config->debug ) {
			error_log( "[GYMActivity::DEBUG] GenyIntranetPage MySQL query : $query", 0 );
		}
		
		$intranet_pages_list = array();
		if( mysql_num_rows( $result ) != 0 ) {
			while( $row = mysql_fetch_row( $result ) ) {
				$tmp_intranet_page = new GenyIntranetPage();
				$tmp_intranet_page->id = $row[0];
				$tmp_intranet_page->title = $row[1];
				$tmp_intranet_page->intranet_category_id = $row[2];
				$tmp_intranet_page->intranet_type_id = $row[3];
				$tmp_intranet_page->status_id = $row[4];
				$tmp_intranet_page->acl_modification_type = $row[5];
				$tmp_intranet_page->profile_id = $row[6];
				$tmp_intranet_page->description = $row[7];
				$tmp_intranet_page->content = gzuncompress( $row[8] );
				$intranet_pages_list[] = $tmp_intranet_page;
			}
		}
		
		if( count( $intranet_pages_list ) == 0 ) {
			return;
		}
		$intranet_page = $intranet_pages_list[0];
		if( isset( $intranet_page ) && $intranet_page->id > -1 ) {
			$this->id = $intranet_page->id;
			$this->title = $intranet_page->title;
			$this->intranet_category_id = $intranet_page->intranet_category_id;
			$this->intranet_type_id = $intranet_page->intranet_type_id;
			$this->status_id = $intranet_page->status_id;
			$this->acl_modification_type = $intranet_page->acl_modification_type;
			$this->profile_id = $intranet_page->profile_id;
			$this->description = $intranet_page->description;
			$this->content = $intranet_page->content;
		}
	}
}
?>