<?php
	
        require_once('../config.php');
	require_once('../course/lib.php');
	/*if(isset($_POST['queryString'])) {
            $queryString = addslashes($_POST['queryString']);
			
            // Is the string length greater than 0?
			
            if(strlen($queryString) >0) {
            // Run the query: We use LIKE '$queryString%'
            // The percentage sign is a wild-card, in my example of countries it works like this...
				
                $query = $DB->get_records_sql( "SELECT mdl_achievement_objectives_usr.context FROM mdl_achievement_objectives_usr WHERE mdl_achievement_objectives_usr.context LIKE '$queryString%' LIMIT 10;" );
                if($query) {
                    foreach ($query as $result) {
                        // Format the results, im using <li> for the list, you can change it.
                        // The onClick function fills the textbox with the result.				
                        // YOU MUST CHANGE: $result->value to $result->your_colum
                        echo '<li onClick="fill(\''.$result->context.'\');">'.$result->context.'</li>';
                    }
                }
            } // There is a queryString.
	} else {
		echo 'There should be no direct access to this script!';
	}*/
        
        $input = $_GET["q"];
        $data = array();
        $query = $DB->get_records_sql( "SELECT mdl_achievement_objectives_usr.id, mdl_achievement_objectives_usr.context FROM mdl_achievement_objectives_usr WHERE mdl_achievement_objectives_usr.context LIKE '$input%' GROUP BY mdl_achievement_objectives_usr.context LIMIT 10;" );
        foreach( $query as $row) {
                $json = array();
                $json['value'] = stripslashes($row->context);
                $json['name'] = stripslashes($row->context);
                $data[] = $json;
        }
        header("Content-type: application/json");
        echo json_encode($data);
?>