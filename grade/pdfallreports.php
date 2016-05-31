<?php

    require_once('../config.php');
    require_once('../course/lib.php');
    require_once('../lib/gradelib.php');
	require_once("$CFG->dirroot/mod/assignment/lib.php");
	
	define("FPDF_FONTPATH","../fpdf/font/");
	require_once("../fpdf/fpdf.php");

	$coursecontext = get_context_instance(CONTEXT_SYSTEM);
	$PAGE->set_context($coursecontext);
	$PAGE->set_url($CFG->wwwroot."/grade/pdfallreports.php");
	$PAGE->set_pagelayout('admin');

    function errormsg($message, $link='') {
        global $CFG, $SESSION;
    
        print_header(get_string('error'));
        echo '<br />';
    
        $message = clean_text($message);
    
        print_simple_box('<span style="font-family:monospace;color:#000000;">'.$message.'</span>', 'center', '', '#FFBBBB', 5, 'errorbox');
    
        if (!$link) {
            if ( !empty($SESSION->fromurl) ) {
                $link = $SESSION->fromurl;
                unset($SESSION->fromurl);
            } else {
                $link = $CFG->wwwroot .'/';
            }
        }
        print_continue($link);
        print_footer();
        die;
    }
	
	function cleanwordHTML($html) {
		$html = preg_replace('/<\s*style.+?<\s*\/\s*style.*?>/si', ' ', $html );
		$html = ereg_replace("<(/)?(font|span|del|ins)[^>]*>","",$html);
		$html = ereg_replace("<([^>]*)(class|lang|style|size|face)=(\"[^\"]*\"|'[^']*'|[^>]+)([^>]*)>","<\\1>",$html);
		$html = ereg_replace("<([^>]*)(class|lang|style|size|face)=(\"[^\"]*\"|'[^']*'|[^>]+)([^>]*)>","<\\1>",$html);
		
		$html = str_replace("</ul>","<br />", $html);
		$html = str_replace("<ul>","<br />-", $html);
		
		$html = strip_tags($html,'<table><tr><td><br>');
		$html = preg_replace("/(<br\s*\/?>\s*)+/", "<br />", $html);
		$html = str_replace("\n", "", $html);
		$html = str_replace("<br />", "\n", $html);
		$html = htmlspecialchars_decode($html);
		$html = html_entity_decode($html);
		
		$find[] = 'â€œ';  // left side double smart quote
		$find[] = 'â€';  // right side double smart quote
		$find[] = 'â€˜';  // left side single smart quote
		$find[] = 'â€™';  // right side single smart quote
		$find[] = 'â€¦';  // elipsis
		$find[] = 'â€”';  // em dash
		$find[] = 'â€“';  // en dash
		$find[] = '“';
		$find[] = '”';
		$find[] = 'é';
		$find[] = 'É';
		$find[] = '•';
		$find[] = '–';

		$replace[] = '"';
		$replace[] = '"';
		$replace[] = "'";
		$replace[] = "'";
		$replace[] = "...";
		$replace[] = "-";
		$replace[] = "-";
		$replace[] = '"';
		$replace[] = '"';
		$replace[] = 'e';
		$replace[] = 'E';
		$replace[] = '-';
		$replace[] = '-';

		$html = str_replace($find, $replace, $html);
		
		return $html;
	}
	
	class tableExtractor 
	{
			
			var $source			= NULL;
			var $anchor            = NULL;
			var $anchorWithin    = false;
			var $headerRow        = false;
			var $startRow        = 0;
			var $maxRows        = 0;
			var $startCol        = 0;
			var $maxCols        = 0;
			var $stripTags        = false;
			var $extraCols        = array();
			var $rowCount        = 0;
			var $dropRows        = NULL;
				
			var $cleanHTML        = NULL;
			var $rawArray        = NULL;
			var $finalArray        = NULL;
				
			function extractTable() {
				
				// check if text has a table
				if ( $this->cleanHTML() ) { 
					$this->prepareArray();
					return $this->createArray();
				} else {
					return FALSE;
				}
			}
			
			function cleanHTML() {
								
				$startSearch = 0;
				
				// extract table
				$startTable = stripos($this->source, '<table', $startSearch);
				
				$beforeText = substr($this->source, $startSearch, $startTable);
				
				if ($startTable === FALSE) {
					// there is no table here
					return FALSE;
				} else {
					$endTable = stripos($this->source, '</table>', $startTable) + 8;
					
					$afterTextLen = strlen($this->source) - $endTable;
					
					$afterText = substr($this->source, $endTable, $afterTextLen); 
					$table = substr($this->source, $startTable, $endTable - $startTable);
				
					if(!function_exists('lcase_tags')) {
						function lcase_tags($input) {
							return strtolower($input[0]);
						}
					}
					
					// lowercase all table related tags
					$table = preg_replace_callback('/<(\/?)(table|tr|th|td)/is', 'lcase_tags', $table);
					
					// remove all thead and tbody tags
					$table = preg_replace('/<\/?(thead|tbody).*?>/is', '', $table);
					
					// replace th tags with td tags
					$table = preg_replace('/<(\/?)th(.*?)>/is', '<$1td$2>', $table);
											
					// clean string
					//$table = trim($table);
					//$table = str_replace("\r\n", "", $table); 
									
					$this->cleanHTML = $table;
					$this->beforeText = $beforeText;
					$this->afterText = $afterText;
					return TRUE;
				}
				
			}
				
			function prepareArray() {
				
				// split table into individual elements
				$pattern = '/(<\/?(?:tr|td).*?>)/is';
				$table = preg_split($pattern, $this->cleanHTML, -1, PREG_SPLIT_DELIM_CAPTURE);    
		 
				// define array for new table
				$tableCleaned = array();
					
				// define variables for looping through table
				$rowCount = 0;
				$colCount = 1;
				$trOpen = false;
				$tdOpen = false;
					
				// loop through table
				foreach($table as $item) {
					
				// trim item
				//$item = str_replace(' ', '', $item);
				$item = trim($item);
						
				// save the item
				$itemUnedited = $item;
						
				// clean if tag                                    
				$item = preg_replace('/<(\/?)(table|tr|td).*?>/is', '<$1$2>', $item);
		 
				// pick item type
				switch ($item) {
					case '<tr>':
						// start a new row
						$rowCount++;
						$colCount = 1;
						$trOpen = true;
						break;
								
					case '<td>':
						// save the td tag for later use
						$tdTag = $itemUnedited;
						$tdOpen = true;
						break;
								
					case '</td>':
						$tdOpen = false;
						break;
								
					case '</tr>':
						$trOpen = false;
						break;
								
					default :
						// if a TD tag is open
						if($tdOpen) {
								// check if td tag contained colspan                                            
								if(preg_match('/<td [^>]*colspan\s*=\s*(?:\'|")?\s*([0-9]+)[^>]*>/is', $tdTag, $matches)) {
									$colspan = $matches[1];
								} else {
									$colspan = 1;
								}
															
								// check if td tag contained rowspan
								if(preg_match('/<td [^>]*rowspan\s*=\s*(?:\'|")?\s*([0-9]+)[^>]*>/is', $tdTag, $matches)) {
									$rowspan = $matches[1];
								} else {
									$rowspan = 0;
								}
										
								// loop over the colspans
								for($c = 0; $c < $colspan; $c++) {
															
									// if the item data has not already been defined by a rowspan loop, set it
									if(!isset($tableCleaned[$rowCount][$colCount])) {
										$tableCleaned[$rowCount][$colCount] = $item;
									} else {
										$tableCleaned[$rowCount][$colCount + 1] = $item;
									}
											
									// create new rowCount variable for looping through rowspans
									$futureRows = $rowCount;
										
									// loop through row spans
									for($r = 1; $r < $rowspan; $r++) {
										$futureRows++;                                    
										if($colspan > 1) {
											$tableCleaned[$futureRows][$colCount + 1] = $item;
										} else {
											$tableCleaned[$futureRows][$colCount] = $item;
										}
									}
			
									// increase column count
									$colCount++;
									
								}
									
								// sort the row array by the column keys (as inserting rowspans screws up the order)
								ksort($tableCleaned[$rowCount]);
							}
							break;
						}    
					}
					// set row count
					if($this->headerRow) {
						$this->rowCount = count($tableCleaned) - 1;
					} else {
						$this->rowCount = count($tableCleaned);
					}
					
					$this->rawArray = $tableCleaned;
					
				}
				
				function createArray() {
					
					// define array to store table data
					$tableData = array();
					
					// get column headers
					if($this->headerRow) {
						// trim string
						$row = $this->rawArray[$this->headerRow];
									
						// set column names array
						$columnNames = array();
						$uniqueNames = array();
								
						// loop over column names
						$colCount = 0;
						foreach($row as $cell) {
										
							$colCount++;
							
							$cell = strip_tags($cell);
							$cell = trim($cell);
							
							// save name if there is one, otherwise save index
							if($cell) {
							
								if(isset($uniqueNames[$cell])) {
									$uniqueNames[$cell]++;
									$cell .= ' ('.($uniqueNames[$cell] + 1).')';    
								}            
								else {
									$uniqueNames[$cell] = 0;
								}
		 
								$columnNames[$colCount] = $cell;
								
							}                        
							else
								$columnNames[$colCount] = $colCount;
							
						}
						
						// remove the headers row from the table
						unset($this->rawArray[$this->headerRow]);
			
					}
					
					// remove rows to drop
					foreach(explode(',', $this->dropRows) as $key => $value) {
						unset($this->rawArray[$value]);
					}
										
					// set the end row
					if($this->maxRows)
						$endRow = $this->startRow + $this->maxRows - 1;
					else
						$endRow = count($this->rawArray);
						
					// loop over row array
					$rowCount = 0;
					$newRowCount = 0;                            
					foreach($this->rawArray as $row) {
					
						$rowCount++;
						
						// if the row was requested then add it
						if($rowCount >= $this->startRow && $rowCount <= $endRow) {
						
							$newRowCount++;
											
							// create new array to store data
							$tableData[$newRowCount] = array();
							
							//$tableData[$newRowCount]['origRow'] = $rowCount;
							//$tableData[$newRowCount]['data'] = array();
							$tableData[$newRowCount] = array();
							
							// set the end column
							if($this->maxCols)
								$endCol = $this->startCol + $this->maxCols - 1;
							else
								$endCol = count($row);
							
							// loop over cell array
							$colCount = 0;
							$newColCount = 0;                                
							foreach($row as $cell) {
							
								$colCount++;
								
								// if the column was requested then add it
								if($colCount >= $this->startCol && $colCount <= $endCol) {
							
									$newColCount++;
									
									if($this->extraCols) {
										foreach($this->extraCols as $extraColumn) {
											if($extraColumn['column'] == $colCount) {
												if(preg_match($extraColumn['regex'], $cell, $matches)) {
													if(is_array($extraColumn['names'])) {
														$this->extraColsCount = 0;
														foreach($extraColumn['names'] as $extraColumnSub) {
															$this->extraColsCount++;
															$tableData[$newRowCount][$extraColumnSub] = $matches[$this->extraColsCount];
														}                                        
													} else {
														$tableData[$newRowCount][$extraColumn['names']] = $matches[1];
													}
												} else {
													$this->extraColsCount = 0;
													if(is_array($extraColumn['names'])) {
														$this->extraColsCount = 0;
														foreach($extraColumn['names'] as $extraColumnSub) {
															$this->extraColsCount++;
															$tableData[$newRowCount][$extraColumnSub] = '';
														}                                        
													} else {
														$tableData[$newRowCount][$extraColumn['names']] = '';
													}
												}
											}
										}
									}
									
									if($this->stripTags)        
										$cell = strip_tags($cell);
									
									// set the column key as the column number
									$colKey = $newColCount;
									
									// if there is a table header, use the column name as the key
									if($this->headerRow)
										if(isset($columnNames[$colCount]))
											$colKey = $columnNames[$colCount];
									
								// add the data to the array
								//$tableData[$newRowCount]['data'][$colKey] = $cell;
								$tableData[$newRowCount][$colKey] = $cell;
							}
						}
					}
				}
							
				$this->finalArray = $tableData;
				return $tableData;
			}    
	}
	
	class PDF extends FPDF
	{
		function Header()
		{
			global $studentname;
			global $studentdetails;
			
			$this->Image('http://online.mhjc.school.nz/file.php/1/logo/MIS_Junior_College_Logo.png',160,10,40);
			$this->SetFont('Times','B',15);
			$this->SetY(10);
			$this->MultiCell(0,10,"Live eReport for\n".$studentname.", ".$studentdetails->department,0,'L');
			$this->SetFont('Times','I',8);
			$y = $this->GetY();
			$this->Cell(0,5,'Page '.$this->PageNo().' of {nb}',0,0,'C');
			$this->SetY($y);
			$this->MultiCell(0,5,date("jS F, Y"),0,'R');
			$this->Ln(1);
		}
	}
	
    require_login();

    if (!has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM))) {
        csverror('You must be an administrator to edit courses in this way.');
    }

    if (! $site = get_site()) {
        csverror('Could not find site-level course');
    }

    if (!$adminuser = get_admin()) {
        csverror('Could not find site admin');
    }

	if ($_SERVER['REQUEST_METHOD'] != 'POST') {
		/// Print the header
		$PAGE->set_title('PDF eReports');
		$PAGE->set_heading('PDF eReports');
		echo $OUTPUT->header();
		echo $OUTPUT->heading('PDF eReports');
		
		/// Print the form
		echo '<center>';
		echo '<form method="post" enctype="multipart/form-data" action="pdfallreports.php">Class: <input type="text" name="search" size="50" value="">'.
			 '<input type="submit" value="PDF">'.
			 '</form></br>';
		echo '</center>';
		echo $OUTPUT->footer();
	} else {
		// The go button has been pressed
		$search = $_POST['search'];
		
		$mysql = "SELECT mdl_user.id, mdl_user.department FROM mdl_user HAVING mdl_user.department = '$search' ORDER BY mdl_user.department;";
		
		$studentinfo = $DB->get_records_sql( $mysql );
		
		if ( $studentinfo ) {
			$outputpath = $CFG->dataroot."/".$search;
			if ( mkdir($outputpath) OR file_exists($outputpath) ) {		
				foreach ($studentinfo as $student)
				{
					$sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_assignment_submissions.assignment, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.id, mdl_assignment.name, mdl_assignment.grade AS scalegrade, mdl_assignment_submissions.grade, mdl_assignment_submissions.submissioncomment, mdl_assignment_submissions.teacher, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid, mdl_grade_outcomes.description
					FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id
					GROUP BY mdl_user.username, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_course.fullname
					HAVING mdl_user.id = '$student->id' AND scalegrade = '-2'
					ORDER BY mdl_course.fullname , mdl_assignment.name;";

					$stuinfo = $DB->get_records_sql( $sql );

					$scalesql = "SELECT mdl_scale.id, mdl_scale.scale FROM mdl_scale WHERE mdl_scale.id=2";
					$scalesrec = $DB->get_record_sql( $scalesql );
					
					$scaleitem = explode (", ", $scalesrec->scale );

					$studentdetails = $DB->get_record('user', array('id'=>$student->id));
					$studentname = fullname( $studentdetails );

					$outputfn = $outputpath."/".$studentname.".pdf";

					$pagelen = 220;

					$pdf=new PDF();
					$pdf->AliasNbPages();
					$pdf->SetTitle("Live eReport for ".$studentname);
					$pdf->AddPage();

					/* MAIN LAYOUT TABLE REPORT CELL */

					$classinfo = array();
					$i = 0;

					foreach($stuinfo as $row)
						{
							$classinfo[$i] = $row->fullname;
							$i++;
						}

					$classunique = array_unique( $classinfo );

					//blank temp class name
					$tempclassname = '';

					$pdf->SetAutoPageBreak(FALSE);

					foreach ($stuinfo as $row)
					{
						$grading_info = grade_get_grades($row->course, 'mod', 'assignment', $row->assignment, $student->id);
						if ( array_key_exists('1000', $grading_info->outcomes) ) {
							$gradeitem = $grading_info->outcomes['1000'];
							$grade = $gradeitem->grades[$student->id];
							$studentmark_task = $grade->str_grade;
						} else {
							$studentmark_task = 'dummy';
						}

						$studentmark_final = $grading_info->items[0]->grades[$student->id]->str_grade;

						$dategraded = date("d-m-Y", $grading_info->items[0]->grades[$student->id]->dategraded);

						/* if ( !$assignment = $DB->get_record('assignment', array('id'=>$row->assignment)) ) {
									error("Course module is incorrect");
								}
								if ( !$course = $DB->get_record('course', array('id'=>$assignment->course)) ) {
									error("Course is misconfigured");
								}
								if (! $cm = get_coursemodule_from_instance("assignment", $assignment->id, $course->id)) {
									error("Course Module ID was incorrect");
						}

						require_once ("$CFG->dirroot/mod/assignment/type/$assignment->assignmenttype/assignment.class.php"); */

						/* $assignmentclass = "assignment_$assignment->assignmenttype";
						$assignmentinstance = new $assignmentclass($cm->id, $assignment, $cm, $course);

						$assignmentdesc = $assignmentinstance->assignment->description; */

						if ( ($studentmark_final == $studentmark_task) AND (count( $scaleitem ) == 4)  )
							{
								if ($row->fullname != $tempclassname)
								{
									$teacherdetails = $DB->get_record('user', array('id'=>$row->teacher));
									$teachername = explode(" ", fullname( $teacherdetails ));
									$teachfn = substr( $teachername[0], 0, 1);
									$teachsn = $teachername[1];
									$pdf->SetFillColor(200);
									$pdf->SetFont('Times','B',9);
									$pdf->SetTextColor(0);
									$pdf->MultiCell(0,6,$row->fullname.", Teacher: ".$teachfn." ".$teachsn,0,'L',true);
								}
								
								$pdf->SetFont('Times','B',9);
								$pdf->SetTextColor(0,99,108);
								$pdf->MultiCell(0,5,$row->name,0,'L');
								$pdf->SetFont('Times','I',9);
								$pdf->MultiCell(0,5,"(".$dategraded.")",0,'L');
								
								$pdf->SetTextColor(0);
								$pdf->SetX(10);
								$pdf->SetFont('Times','B',9);
								$pdf->MultiCell(0,5,"Current Level of Achievement:",0,'L');
								$pdf->SetX(20);
							
								$cellwidth = (1 / count( $scaleitem )) * 160;
								foreach ($scaleitem as $tabletext)
								{
									if ($studentmark_task == $tabletext)
									{
										$pdf->SetTextColor(255);
										$pdf->SetFillColor(0,99,108);
										$pdf->Cell($cellwidth,6,$tabletext,1,0,'C',true);
										$pdf->SetTextColor(0);
									} else {
										$pdf->SetFillColor(255);
										$pdf->Cell($cellwidth,6,$tabletext,1,0,'C');
									}
								}
								
								$pdfdesc = cleanwordHTML($row->description);
								
								$tbl = new tableExtractor;
								$tbl->source = $pdfdesc;
								$d = $tbl->extractTable();
								
								if ( $pdf->GetY() > $pagelen ) {
									$pdf->AddPage();
								}
								
								$pdf->MultiCell(0,8,"",0);
								$pdf->SetX(10);
								$pdf->MultiCell(0,5,"Task Description:",0,'L');
								$pdf->SetFont('');
								
								if ( $d ) {
									$pdf->SetX(20);
									
									if ($tbl->beforeText) {
										$pdf->MultiCell(0,4,$tbl->beforeText,0,'L');
									}
									
									if ( $pdf->GetY() > $pagelen ) {
										$pdf->AddPage();
									}
									
									foreach( $d as $tablerow)
									{
										$startx = 20;
										$starty = $pdf->GetY();
										
										$pdf->SetX($startx);
										$cellwidth = (1 / count( $tablerow )) * 160;
										$yPos = $pdf->GetY();
										$xPos = $pdf->GetX();
										
										$prevy = 0;
										foreach( $tablerow as $tablecell )
										{
											$pdf->SetY($yPos);
											$pdf->SetX($xPos);
											$pdf->MultiCell($cellwidth,4,$tablecell,0,'L');
											$xPos = $xPos + $cellwidth;
											
											$stopy = $pdf->GetY();
											if ($prevy > $stopy) {
												$stopy = $prevy;
											} else {
												$prevy = $stopy;
											}
										}
										
										$stopx = $xPos;
										
										$bordwidth = $stopx - $startx;
										$bordheight = $stopy - $starty;
										
										$pdf->Rect($startx, $starty, $bordwidth, $bordheight);
										
										$xPos = $startx;
										foreach( $tablerow as $tablecell )
										{
											$pdf->Line($xPos,$starty,$xPos,$stopy);
											$xPos = $xPos + $cellwidth;
										}
										$pdf->SetY($stopy);
									}
									
									if ( $pdf->GetY() > $pagelen ) {
										$pdf->AddPage();
									}
									
									$pdf->SetX(20);
									$pdf->MultiCell(0,4,$tbl->afterText,0,'L');
								} else {
									$pdf->SetX(20);
									$pdf->MultiCell(0,4,cleanwordHTML($pdfdesc),0,'L');
								}
								
								if ( $pdf->GetY() > $pagelen ) {
									$pdf->AddPage();
								}

								$pdf->MultiCell(0,6,"",0);
								$pdf->SetFont('Times','B',9);
								$pdf->SetX(10);
								$pdf->MultiCell(0,5,"Teacher Feedback:",0,'L');
								$pdf->SetFont('');
								
								$pdffeed = cleanwordHTML($row->submissioncomment);
								
								$atbl = new tableExtractor;
								$atbl->source = $pdffeed;
								$e = $atbl->extractTable();
								
								if ( $e ) {
									$pdf->SetX(20);
									$pdf->MultiCell(0,4,$atbl->beforeText,0,'L');

									if ( $pdf->GetY() > $pagelen ) {
										$pdf->AddPage();
									}
									
									foreach( $e as $tablerow)
									{
										$startx = 20;
										$starty = $pdf->GetY();
										
										$pdf->SetX($startx);
										$cellwidth = (1 / count( $tablerow )) * 160;
										$yPos = $pdf->GetY();
										$xPos = $pdf->GetX();
										
										$prevy = 0;
										foreach( $tablerow as $tablecell )
										{
											$pdf->SetY($yPos);
											$pdf->SetX($xPos);
											$pdf->MultiCell($cellwidth,4,$tablecell,0,'L');
											$xPos = $xPos + $cellwidth;
											
											$stopy = $pdf->GetY();
											if ($prevy > $stopy) {
												$stopy = $prevy;
											} else {
												$prevy = $stopy;
											}
										}
										
										$stopx = $xPos;
										
										$bordwidth = $stopx - $startx;
										$bordheight = $stopy - $starty;
										
										$pdf->Rect($startx, $starty, $bordwidth, $bordheight);
										
										$xPos = $startx;
										foreach( $tablerow as $tablecell )
										{
											$pdf->Line($xPos,$starty,$xPos,$stopy);
											$xPos = $xPos + $cellwidth;
										}
										$pdf->SetY($stopy);
									}
									
									if ( $pdf->GetY() > $pagelen ) {
										$pdf->AddPage();
									}
									
									$pdf->SetX(20);
									$pdf->MultiCell(0,4,$atbl->afterText,0,'L');
								} else {
									$pdf->SetX(20);
									$pdf->MultiCell(0,4,cleanwordHTML($pdffeed),0,'L');
								}
								$pdf->MultiCell(0,6,"",0);
								$tempclassname = $row->fullname;
							}
					}

					$pdf->Output($outputfn,'F');
				}
		}
		}
	}
?>