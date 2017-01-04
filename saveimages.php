
<?php include 'connection.php';?>

<?php
/* error_reporting(0);   
$dir=substr(uniqid(),-7); */
require_once "Classes/PHPExcel.php";
  $valid_formats = array("jpg", "png", "gif", "jpeg");
$max_file_size = 	10240*10000; //10000 kb

 objPHPExcel = new PHPExcel();
            $objWorkSheet = $objPHPExcel->createSheet();            

             // Set the active Excel worksheet to sheet 0 
            $objPHPExcel->setActiveSheetIndex(0);  

            //Taslak Verileri
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.'1', 'Firm'); 
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.'1', 'SFUFORMU - FR.PS.21'); 
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.'3', 'NO:'); 
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.'2', 'Name Surname Signature'); 
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.'4', 'Date');
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.'5', 'Stock No:'); 
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.'5', 'Image'); 
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.'5', 'Image'); 
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.'5', 'Resim'); 
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.'5', 'Image'); 
            $objPHPExcel->getActiveSheet()->SetCellValue('K'.'5', 'Quantity'); 
            $objPHPExcel->getActiveSheet()->SetCellValue('M'.'5', 'Price'); 



     
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel); 
            $objWriter->save('some_excel_file.xlsx'); 

/* $path = "Prototype/uploads/"; // Upload directory
mkdir ($path, 0744); */
$count = 0;

	$skippedimages = new SplQueue();
	$imported = new SplQueue();

if (isset($_POST['search'])) {
	// Loop $_FILES to exeicute all files
	foreach ($_FILES['files']['name'] as $f => $name) {   
	
			
	    if ($_FILES['files']['error'][$f] == 4) {
	      

		  continue; // Skip file if any error found
			//echo "something <br>";
	    }	       
	    if ($_FILES['files']['error'][$f] == 0) {	           
	        if ($_FILES['files']['size'][$f] > $max_file_size) {
	            $message[] = "$name is too large!.";
				//echo "something***************** <br>";
	            continue; // Skip large files
	        }
			elseif( ! in_array(pathinfo($name, PATHINFO_EXTENSION), $valid_formats) ){
				$message[] = "$name is not a valid format";
				//echo "something+++++++++++++++++++ <br>";
				//echo "$name-- ";
				continue; // Skip invalid file formats
			}
	        else{ // No error found! Move uploaded files 
	          //  if(move_uploaded_file($_FILES["files"]["tmp_name"][$f], $path.$name))
	           // $count=$count+1; // Number of successfully uploaded file
			
				//echo $path.$name;
		
				 $image = addslashes(file_get_contents($_FILES['files']['tmp_name'][$f]));

				$image_name = addslashes($_FILES['files']['name'][$f]);

				$ext = pathinfo($image_name, PATHINFO_EXTENSION);
				
				$withoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $image_name);
				
				$Label_UPC = $withoutExt;
				
				
			$flag = false;	
			$query = "Select * from $dbname.Package where Label_UPC = '$Label_UPC'"; 
			$result = mysqli_query($conn,$query);
			
			$rowcount=mysqli_num_rows($result);
			
			if($rowcount>0){
		
			
			
				while($row = $result->fetch_assoc()) {
					
					
					$ID =  $row['PackageID'];
					$query2 = "Insert into $dbname.Images (Image, ImageName,LabelID,Extension) VALUES ('$image', '$withoutExt', '$ID','$ext')";	 
					$result2 = mysqli_query($conn,$query2);
					if(!$result2){
						
						continue;
					}else{
						
						$flag =true;
					}
					
					
				}
			
			if($flag) {
				++$count; 
				$withoutExt .= "-$count";
				$imported->push($withoutExt);
				}
					
				
				
				
				
			}else{
				
				//echo "Label UPC not found $Label_UPC";
				$skippedimages->push($withoutExt);
			}
			 //Number of successfully uploaded file

	        }
	    }
	}
	
	echo "<h3>$count files were imported <h3>";
	


   while (!$imported->isEmpty()) { 
                            
        $senditem = $imported->shift(); 
		if(strlen ($senditem) < 1) continue;
		echo "$senditem <br>";
   }	

 
	echo "<h4>Skipped Images</h4>";
   while (!$skippedimages->isEmpty()) { 
                            
        $senditem = $skippedimages->shift(); 
		if(strlen ($senditem) < 1) continue;
		echo "$senditem <br>";
   }  
  }
  
  
  
	
    //show success message
/*     echo "<h1>Uploaded:</h1>";    
    if(is_array($files)){
        echo "<ul>";
        foreach($files as $file){
            echo "<li>$file</li>";
        }
        echo "</ul>";
    } */

?>
	
 	
	
	
	
	
	
	