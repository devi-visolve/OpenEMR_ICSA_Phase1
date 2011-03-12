<?php
//  ------------------------------------------------------------------------ //
//                     Garden State Health Systems                           //
//                    Copyright (c) 2010 gshsys.com                          //
//                      <http://www.gshsys.com/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //


	$result = getProblemData();
	$row = sqlFetchArray($result);
	$pCount =0;
	//while ($row = sqlFetchArray($result)) {
	
	do {
		
		$pCount++;
		echo 'encounter :'.$row['encounter'].'\n';

		$e_Problem = $ccr->createElement('Problem');
		$e_Problems->appendChild($e_Problem);

		$e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', 'PROB'.$pCount);
		$e_Problem->appendChild($e_CCRDataObjectID);

		$e_DateTime = $ccr->createElement('DateTime');
		$e_Problem->appendChild($e_DateTime);
		
		$date = date_create($row['date']);
		
		$e_ExactDateTime = $ccr->createElement('ExactDateTime', $date->format('Y-m-d\TH:i:s\Z'));
		$e_DateTime->appendChild($e_ExactDateTime);
		
		$e_IDs = $ccr->createElement('IDs');
		$e_Problem->appendChild($e_IDs);
		
		$e_ID = $ccr->createElement('ID', $row['pid']);
		$e_IDs->appendChild($e_ID);

		$e_IDs->appendChild(sourceType($ccr, $authorID));
		
		$e_Type = $ccr->createElement('Type');
		$e_Problem->appendChild($e_Type);

		$e_Text = $ccr->createElement('Text', $row['prob_title']);
		$e_Type->appendChild($e_Text);
		
		$e_Description = $ccr->createElement('Description' );
		$e_Problem->appendChild($e_Description);

		$e_Text = $ccr->createElement('Text', $row['code_text']);
		$e_Description->appendChild($e_Text);

		$e_Code = $ccr->createElement('Code');
		$e_Description->appendChild($e_Code);

		$e_Value = $ccr->createElement('Value',$row['diagnosis']);
		$e_Code->appendChild($e_Value);
		
		$e_Status = $ccr->createElement('Status');
		$e_Problem->appendChild($e_Status);

		// $e_Text = $ccr->createElement('Text', $row['outcome']);
		$e_Text = $ccr->createElement('Text', 'Active');
		$e_Status->appendChild($e_Text);
		
		$e_CommentID = $ccr->createElement('CommentID', $row['comments']);
		$e_Problem->appendChild($e_CommentID);
		
		$e_Episodes = $ccr->createElement('Episodes' );
		$e_Problem->appendChild($e_Episodes);

		$e_Number = $ccr->createElement('Number');
		$e_Episodes->appendChild($e_Number);
	
		$e_Episode = $ccr->createElement('Episode');
		$e_Episodes->appendChild($e_Episode);
	
		$e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', 'EP'.$pCount);
		$e_Episode->appendChild($e_CCRDataObjectID);

		$e_Episode->appendChild(sourceType($ccr, $authorID));
		
		$e_Episodes->appendChild(sourceType($ccr, $authorID));
		
		$e_HealthStatus = $ccr->createElement('HealthStatus' );
		$e_Problem->appendChild($e_HealthStatus);

		$e_DateTime = $ccr->createElement('DateTime');
		$e_HealthStatus->appendChild($e_DateTime);

		$e_ExactDateTime = $ccr->createElement('ExactDateTime' );
		$e_DateTime->appendChild($e_ExactDateTime);

		$e_Description = $ccr->createElement('Description' );
		$e_HealthStatus->appendChild($e_Description);

		$e_Text = $ccr->createElement('Text',$row['reason']);
		$e_Description->appendChild($e_Text);
	
		$e_HealthStatus->appendChild(sourceType($ccr, $authorID));
	
	} while ($row = sqlFetchArray($result));
	//}

	// complex type should go in different find and should be included in createCCR.php
/*
	function sourceType($ccr, $uuid){
		
		$e_Source = $ccr->createElement('Source');
		
		$e_Actor = $ccr->createElement('Actor');
		$e_Source->appendChild($e_Actor);
		
		$e_ActorID = $ccr->createElement('ActorID',$uuid);
		$e_Actor->appendChild($e_ActorID);
		
		return $e_Source;
	}
*/
?>

