<?php include 'connection.php';?>
<?php

require_once "Classes/PHPExcel.php";


if (isset($_POST['search'])) {
	error_reporting(E_ALL);
ini_set('display_errors', 1); 


	if (is_uploaded_file($_FILES['file_save']['tmp_name'])) {
		echo "<h3>" . "File ". $_FILES['file_save']['name'] ." uploaded successfully." . "</h3>";

		//	readfile($_FILES['file_save']['tmp_name']);

		$tmpfname = $_FILES['file_save']['tmp_name'];

		echo "0";
		$excelReader = PHPExcel_IOFactory::createReaderForFile($tmpfname);
		echo "1";
		$excelObj = $excelReader->load($tmpfname);
		echo "2";
		$worksheet = $excelObj->getSheet(0);
		echo "3";
		$lastRow = $worksheet->getHighestRow();

		echo "4";

		// IF THE FILE DOES NOT EXIST, WRITE TO IT AS YOU OPEN UP A STREAM,
		// OTHERWISE, JUST APPEND TO IT...

		// OPEN THE FILE FOR WRITING OR APPENDING...

		$count_skipped = 0;
		$skipped_sales = new SplQueue();
		$new_product   = new SplQueue();
		$linked_sales  = new SplQueue();

		for ($row = 2; $row <= $lastRow; $row++) {

			//echo "in<br>";
			$Record                    = $worksheet->getCell('A'.$row)->getValue();
			$Sales_UPC                 = $worksheet->getCell('B'.$row)->getValue();
			$Sales_Description         = $worksheet->getCell('C'.$row)->getValue();
			$Brand                     = $worksheet->getCell('D'.$row)->getValue();
			$Manufacturer              = $worksheet->getCell('E'.$row)->getValue();
			//$Package_Size            = $data[4];
			$Rank                      = $worksheet->getCell('F'.$row)->getValue();
			$Dollar_Volume             = $worksheet->getCell('G'.$row)->getValue();
			$Shr                       = $worksheet->getCell('H'.$row)->getValue();
			$Vol_PerCent_Change        = $worksheet->getCell('I'.$row)->getValue();
			$Kilo_Vol                  = $worksheet->getCell('J'.$row)->getValue();
			$Kilo_Shr                  = $worksheet->getCell('K'.$row)->getValue();
			$Kilo_Vol_PerCent_Change   = $worksheet->getCell('L'.$row)->getValue();
			$Avg_AC_Disk               = $worksheet->getCell('M'.$row)->getValue();
			$Ave_Retail_Unit_Price     = $worksheet->getCell('N'.$row)->getValue();
			$Source                    = $worksheet->getCell('O'.$row)->getValue();
			//$Neilsen_Category_Number = $data[14];
			$Nielsen_Category          = $worksheet->getCell('P'.$row)->getValue();
			$Collection_Date           = $worksheet->getCell('Q'.$row)->getValue();
			$Sales_Year                = $worksheet->getCell('R'.$row)->getValue();
			$Control_Label             = $worksheet->getCell('S'.$row)->getValue();
			$Kilo_Vol_Total            = $worksheet->getCell('T'.$row)->getValue();
			$Kilo_Vol_Rank             = $worksheet->getCell('U'.$row)->getValue();
			$Dollar_Volume_Total       = $worksheet->getCell('V'.$row)->getValue();
			$Cluster_Number            = $worksheet->getCell('W'.$row)->getValue();
			$Product_Grouping          = $worksheet->getCell('X'.$row)->getValue();
			$Product_Description       = $worksheet->getCell('Y'.$row)->getValue();
			$Classification_Number     = $worksheet->getCell('Z'.$row)->getValue();
			$Classification_Type       = $worksheet->getCell('AA'.$row)->getValue();
			$Comments                  = $worksheet->getCell('AB'.$row)->getValue();

			if ($Sales_UPC == null or $Sales_Description == null or $Kilo_Vol == null or $Source == null or $Sales_Year == null or $Collection_Date == null) {

				if ($Sales_Description) {

					$input1 = "Record : $Record, $Sales_Description";
					$skipped_sales->push($input1);
					//**********************

					continue;
				} else if ($Sales_UPC != null and $Sales_Description == null) {

					$input2 = "Record : $Record, $Sales_UPC";
					$skipped_sales->push($input2);
					//fwrite($myfile, $Sales_Description);

					continue;

				} else {

					$inputX = "Record : $Record, No Description and Sales UPC available";
					$skipped_sales->push($inputX);

					continue;
				}

			} else {
				if (preg_match('/^\d+$/', $Product_Grouping)) {

					/* Check if grouping already exists in Market Share table */
					$check_grouping =<<<EOQ
SELECT *
  FROM Sales
 WHERE Sales_UPC = ?
EOQ;
// AND ProductIDS <> (Select Distinct ProductIDS From $dbname.Sales Where Product_Grouping= $Product_Grouping)";

					$stmt = $conn->prepare($check_grouping);
					$stmt->bind_param("s", $Sales_UPC);
					$result_grouping = $stmt->execute();
					$stmt->store_result();
					// $result_grouping = mysqli_query($conn, $check_grouping);

					//$rowcount1 = mysqli_num_rows($result_grouping);
					if (($stmt->num_rows)>0){
		/* 		echo "Sales_UPC Code already belongs to a different product";
				$skipped_sales->push($Description);
					continue; */

						$param = array(
							$Sales_UPC,
							$Source,
							$Sales_Description,
							$Sales_Year,
							$Nielsen_Category,
							$Kilo_Vol,
							$Dollar_Volume,
							$Brand,
							$Rank,
							$Vol_PerCent_Change,
							$Kilo_Shr,
							$Kilo_Vol_PerCent_Change,
							$Avg_AC_Disk,
							$Ave_Retail_Unit_Price,
							$Collection_Date,
							$Dollar_Volume_Total,
							$Product_Grouping
						);

						$insert_query =<<<EOQ
INSERT INTO Sales (
       ProductIDS,
       Sales_UPC,
       Source,
       Sales_Description,
       Sales_Year,
       Nielsen_Category,
       Kilo_Vol,
       Dollar_Volume,
       Brand,
       Dollar_Rank,
       Dollar_Volume_PerCentage_Change,
       Kilo_Share,
       Kilo_Volume_Percent_Change,
       Average_AC_Dist,
       Average_Retail_Price,
       Collection_Date,
       Dollar_Volume_Total,
       Product_Grouping
)
SELECT DISTINCT ProductIDS, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
  FROM Sales
 WHERE Sales_UPC = ?
EOQ;

						$stmt = $conn->prepare($insert_query);
						$stmt->bind_param("iissssddsdsdsdddsdii", $param[0], $param[1], $param[2], $param[3], $param[4], $param[5], $param[6], $param[7], $param[8], $param[9], $param[10], $param[11], $param[12], $param[13], $param[14], $param[15], $param[16], $param[17], $Sales_UPC);
						$result_insert = $stmt->execute();

						// $result_insert = mysqli_query($conn, $insert_query);

						if ($Product_Description != null) {

							$query_update =<<<EOQ
UPDATE Product
   SET Description = ?
 WHERE ProductID = (
SELECT DISTINCT ProductIDS
  FROM Sales
 WHERE Sales_UPC = ?
)
EOQ;

							$stmt = $conn->prepare($query_update);
							$stmt->bind_param("ss", $Product_Description, $Sales_UPC);
							$result_update = $stmt->execute();

							// $result_update = mysqli_query($conn, $query_update);

						}

						$input3= "Record : $Record, $Sales_Description";
						$linked_sales->push($input3);

					} else {

						$grouping =<<<EOQ
SELECT DISTINCT SalesID
  FROM Sales
 WHERE Product_Grouping = ?
EOQ;

						$stmt = $conn->prepare($grouping);
						$stmt->bind_param("", $Product_Grouping);
						$check_grouping_result = $stmt->execute();
						$stmt->store_result();
						// $check_grouping_result = mysqli_query($conn, $grouping);

						//$row_count_grouping = mysqli_num_rows($check_grouping_result);
						if (($stmt->num_rows)>0) {

							$param = array(
								$Sales_UPC,
								$Source,
								$Sales_Description,
								$Sales_Year,
								$Nielsen_Category,
								$Kilo_Vol,
								$Dollar_Volume,
								$Brand,
								$Rank,
								$Vol_PerCent_Change,
								$Kilo_Shr,
								$Kilo_Vol_PerCent_Change,
								$Avg_AC_Disk,
								$Ave_Retail_Unit_Price,
								$Collection_Date,
								$Dollar_Volume_Total,
								$Product_Grouping
							);

							/* Grouping exist, so link market share to that product*/
							$insert_query =<<<EOQ
INSERT INTO Sales (
       ProductIDS,
       Sales_UPC,
       Source,
       Sales_Description,
       Sales_Year,
       Nielsen_Category,
       Kilo_Vol,
       Dollar_Volume,
       Brand,
       Dollar_Rank,
       Dollar_Volume_PerCentage_Change,
       Kilo_Share,
       Kilo_Volume_Percent_Change,
       Average_AC_Dist,
       Average_Retail_Price,
       Collection_Date,
       Dollar_Volume_Total,
       Product_Grouping
)
SELECT DISTINCT ProductIDS, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
  FROM Sales
 WHERE Product_Grouping = ?
EOQ;

							$stmt = $conn->prepare($insert_query);
							$stmt->bind_param("iissssddsdsdsdddsdii", $param[0], $param[1], $param[2], $param[3], $param[4], $param[5], $param[6], $param[7], $param[8], $param[9], $param[10], $param[11], $param[12], $param[13], $param[14], $param[15], $param[16], $param[17], $Product_Grouping);
							$result_insert = $stmt->execute();

							// $result_insert = mysqli_query($conn, $insert_query);

							$input4 = "Record : $Record, $Sales_Description";
							$linked_sales->push($input4);

							if ($Product_Description != null) {

								$query_update =<<<EOQ
UPDATE Product
   SET Description = ?
 WHERE ProductID = (
       SELECT DISTINCT ProductIDS
         FROM Sales
        WHERE Product_Grouping = ?
)
EOQ;

								$stmt = $conn->prepare($query_update);
								$stmt->bind_param("si", $Product_Description, $Product_Grouping);
								$result_update = $stmt->execute();

								// $result_update = mysqli_query($conn, $query_update);
							}

							//echo "$insert_query";


						} else if (($stmt->num_rows)<1)  {

							/* Create a new Product */

							if ($Product_Description == null) {
								$Product_Description = $Sales_Description;
							}

							$param = array($Product_Description, $Brand, $Manufacturer, $Cluster_Number);

							$query =<<<EOQ
INSERT INTO Product (
       Description,
       Brand,
       Manufacturer,
       Cluster_Number
)
VALUES (?, ?, ?, ?)
EOQ;
							$stmt = $conn->prepare($query);
							$stmt->bind_param("sssd", $param[0], $param[1], $param[2], $param[3]);
							$result = $stmt->execute();

							// $result = mysqli_query($conn, $query);

							$id = mysqli_insert_id($conn);
							$input5 = "Record : $Record, $Product_Description";
							$new_product->push($input5);

							$param = array(
								$id,
								$Sales_UPC,
								$Source,
								$Sales_Description,
								$Sales_Year,
								$Nielsen_Category,
								$Kilo_Vol,
								$Dollar_Volume,
								$Brand,
								$Rank,
								$Vol_PerCent_Change,
								$Kilo_Shr,
								$Kilo_Vol_PerCent_Change,
								$Avg_AC_Disk,
								$Ave_Retail_Unit_Price,
								$Collection_Date,
								$Dollar_Volume_Total,
								$Product_Grouping
							);

							$insert_query2 =<<<EOQ
INSERT INTO Sales (
       ProductIDS,
       Sales_UPC,
       Source,
       Sales_Description,
       Sales_Year,
       Nielsen_Category,
       Kilo_Vol,
       Dollar_Volume,
       Brand,
       Dollar_Rank,
       Dollar_Volume_PerCentage_Change,
       Kilo_Share,
       Kilo_Volume_Percent_Change,
       Average_AC_Dist,
       Average_Retail_Price,
       Collection_Date,
       Dollar_Volume_Total,
       Product_Grouping
)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
EOQ;

							$stmt = $conn->prepare($insert_query2);
							$stmt->bind_param("iissssddsdsdsdddsdi", $param[0], $param[1], $param[2], $param[3], $param[4], $param[5], $param[6], $param[7], $param[8], $param[9], $param[10], $param[11], $param[12], $param[13], $param[14], $param[15], $param[16], $param[17]);
							$result_insert = $stmt->execute();

							// $result_insert = mysqli_query($conn, $insert_query2);

							if (!$result_insert) {
								echo "error";
							}


							//$query2 = "INSERT INTO  $dbname.Classification (Number, Classification_Name, _Type) VALUES ('$classificationid', '$classification_name', '$type')";
							//$query1_ ="Insert into $dbname.Product_Component(PackageID, ComponentID, Amount, Amount_Unit_Of_Measure, Daily_Value, PPD) Select PackageID, 1, '$energy_amount_sold', '$energy_unit_sold', '$energy_dvalue_sold', TRUE from $dbname.Package where UPC12 = '$UPC12'";
							if (strlen($Classification_Number) != 0) {
								/* 		Must check if Classification number exist */

								$classification_check =<<<EOQ
SELECT *
  FROM Classification
 WHERE Classification_Number = ?
EOQ;
								$stmt = $conn->prepare($classification_check);
								$stmt->bind_param("d", $Classification_Number);
								$classification_check_result = $stmt->execute();

								// $classification_check_result = mysqli_query($conn, $classification_check);

								$classification_check_count = mysqli_num_rows($classification_check_result);
								if ($classification_check_count > 0) {

									$query2 =<<<EOQ
INSERT INTO Product_Classification (ClassificationID, ProductID)
SELECT ClassificationID, ?
  FROM Classification
 WHERE Classification_Number = ?
EOQ;

									$stmt = $conn->prepare($query2);
									$stmt->bind_param("id", $id, $Classification_Number);
									$result2 = $stmt->execute();

									// $result2 = mysqli_query($conn, $query2);

								} else {
									if (strlen($Classification_Type) != 0) {

										$query3 =<<<EOQ
INSERT INTO Classification (Classification_Number, Classification_Type)
VALUES (?, ?)
EOQ;

										$stmt = $conn->prepare($query3);
										$stmt->bind_param("ds", $Classification_Number, $Classification_Type);
										$result3 = $stmt->execute();

										// $result3 = mysqli_query($conn, $query3);

										$id1 =  mysqli_insert_id($conn);

										$queryC =<<<EOQ
INSERT INTO Product_Classification (ClassificationID, ProductID)
VALUES (?, ?)
EOQ;

										$stmt = $conn->prepare($queryC);
										$stmt->bind_param("ii", $id1, $id);
										$resultC = $stmt->execute();

										// $resultC = mysqli_query($conn, $queryC);
									}
								}
							}

		/* 	$input6= "Record : $Record, $Sales_Description";
					$new_product->push($input6); */

						//echo "$insert_query2";
						//}

						}
					}
				}	//}
				else if (!preg_match('/^\d+$/', $Product_Grouping)) {
					if ($Sales_UPC == null or $Sales_Description == null) {
						//echo "Missing Fields";
						$skipped_sales->push($Sales_Description);
						continue;
					} else {

						if ($Product_Description == null) {
							$Product_Description = $Sales_Description;
						}
						$check_grouping =<<<EOQ
SELECT * FROM Sales WHERE Sales_UPC = ?
EOQ;
// AND ProductIDS <> (Select Distinct ProductIDS From $dbname.Sales Where Product_Grouping= $Product_Grouping)";

						$stmt = $conn->prepare($check_grouping);
						$stmt->bind_param("i", $Sales_UPC);
						$result_grouping = $stmt->execute();
						$stmt->store_result();
						// $result_grouping = mysqli_query($conn, $check_grouping);

						//$rowcount1 = mysqli_num_rows($result_grouping);
						if (($stmt->num_rows)> 0) {

							$param = array(
								$Sales_UPC,
								$Source,
								$Sales_Description,
								$Sales_Year,
								$Nielsen_Category,
								$Kilo_Vol,
								$Dollar_Volume,
								$Brand,
								$Rank,
								$Vol_PerCent_Change,
								$Kilo_Shr,
								$Kilo_Vol_PerCent_Change,
								$Avg_AC_Disk,
								$Ave_Retail_Unit_Price,
								$Collection_Date,
								$Dollar_Volume,
								$Product_Grouping
							);

							$insert_query =<<<EOQ
INSERT INTO Sales (

       ProductIDS,                      -- i
       Sales_UPC,                       -- i
       Source,                          -- s
       Sales_Description,               -- s
       Sales_Year,                      -- s
       Nielsen_Category,                -- s
       Kilo_Vol,                        -- s
       Dollar_Volume,                   -- s
       Brand,                           -- s
       Dollar_Rank,                     -- s
       Dollar_Volume_PerCentage_Change, -- s
       Kilo_Share,                      -- s
       Kilo_Volume_Percent_Change,      -- s
       Average_AC_Dist,                 -- s
       Average_Retail_Price,            -- s
       Collection_Date,                 -- s
       Dollar_Volume_Total,             -- s
       Product_Grouping                 -- s

)
SELECT DISTINCT ProductIDS, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
  FROM Sales
 WHERE Sales_UPC = ?
EOQ;

							$stmt = $conn->prepare($insert_query);
							$stmt->bind_param("iissssssssssssssssi", $param[0], $param[1], $param[2], $param[3], $param[4], $param[5], $param[6], $param[7], $param[8], $param[9], $param[10], $param[11], $param[12], $param[13], $param[14], $param[15], $param[16], $Sales_UPC);
							$result_insert = $stmt->execute();

							// $result_insert = mysqli_query($conn, $insert_query);

							$input7 = "Record : $Record, $Sales_Description";
							$linked_sales->push($input7);
							continue;
						} else {
							$query =<<<EOQ
INSERT INTO Product (Description, Brand, Manufacturer, Cluster_Number) VALUES (?, ?, ?, ?)
EOQ;

							$stmt = $conn->prepare($query);
							$stmt->bind_param("sssd", $Product_Description, $Brand, $Manufacturer, $Cluster_Number);
							$result = $stmt->execute();

							// $result = mysqli_query($conn, $query);

							$id =  mysqli_insert_id($conn);
							$in = "Record : $Record, $Product_Description";
							$new_product->push($in);

							$query3 =<<<EOQ
SELECT *
  FROM Sales
 WHERE Sales_UPC = ?
   AND ProductIDS <> ?
EOQ;

							$stmt = $conn->prepare($query3);
							$stmt->bind_param("ii", $Sales_UPC, $id);
							$result3 = $stmt->execute();

							// $result3 = mysqli_query($conn, $query3);

							$stmt->store_result();
							$rowcount2 = $stmt->num_rows; // mysqli_num_rows($result3);
							if ($rowcount2 > 0) {
								//echo "Sales_UPC Code already belongs to a different product";
								$input8 = "Record : $Record, $Sales_Description";

								$skipped_sales->push($input8);

								continue;

							} else {

								$param = array(
									$id,
									$Sales_UPC,
									$Source,
									$Sales_Description,
									$Sales_Year,
									$Nielsen_Category,
									$Kilo_Vol,
									$Dollar_Volume,
									$Brand,
									$Rank,
									$Vol_PerCent_Change,
									$Kilo_Shr,
									$Kilo_Vol_PerCent_Change,
									$Avg_AC_Disk,
									$Ave_Retail_Unit_Price,
									$Collection_Date,
									$Dollar_Volume,
									$Product_Grouping
								);

								$insert_queryt =<<<EOQ
INSERT Into Sales (

       ProductIDS,                      -- i
       Sales_UPC,                       -- i
       Source,                          -- s
       Sales_Description,               -- s
       Sales_Year,                      -- s
       Nielsen_Category,                -- s
       Kilo_Vol,                        -- s
       Dollar_Volume,                   -- s
       Brand,                           -- s
       Dollar_Rank,                     -- s
       Dollar_Volume_PerCentage_Change, -- s
       Kilo_Share,                      -- s
       Kilo_Volume_Percent_Change,      -- s
       Average_AC_Dist,                 -- s
       Average_Retail_Price,            -- s
       Collection_Date,                 -- s
       Dollar_Volume_Total,             -- s
       Product_Grouping                 -- s

)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
EOQ;

								$stmt = $conn->prepare($insert_queryt);
								$stmt->bind_param("iissssssssssssssss", $param[0], $param[1], $param[2], $param[3], $param[4], $param[5], $param[6], $param[7], $param[8], $param[9], $param[10], $param[11], $param[12], $param[13], $param[14], $param[15], $param[16], $param[17]);
								$result_insertt = $stmt->execute();

								// $result_insertt = mysqli_query($conn, $insert_queryt);

								if (!$result_insertt) {
									echo "error 2 $insert_queryt";
								}
							}



							if (strlen($Classification_Number) != 0) {
								/* Must check if Classification number exist */

								$classification_check =<<<EOQ
SELECT *
  FROM Classification
 WHERE Classification_Number = ?
EOQ;

								$stmt = $conn->prepare($classification_check);
								$stmt->bind_param("s", $Classification_Number);
								$classification_check_result = $stmt->execute();

								// $classification_check_result = mysqli_query($conn, $classification_check);
								$stmt->store_result();
								//$classification_check_count = mysqli_num_rows($classification_check_result);
								if (($stmt->num_rows) > 0) {
									$query2 =<<<EOQ
INSERT INTO Product_Classification (ClassificationID, ProductID)
SELECT ClassificationID, ?
  FROM Classification
 WHERE Classification_Number = ?
EOQ;

									$stmt = $conn->prepare($query2);
									$stmt->bind_param("ii", $id, $Classification_Number);
									$result2 = $stmt->execute();

									// $result2 = mysqli_query($conn, $query2);
								}
							}
						}
					}
				}
			}
		}

		//echo "<h3> Number of product skipped $count_skipped</h3>";
		echo "<h2>Skipped Market Share</h2>";
		while (!$skipped_sales->isEmpty()) {

			$senditem = $skipped_sales->shift();
			if (strlen ($senditem) < 1) continue;
			echo "$senditem <br>";
		}
		echo "<h2>New Products</h2>";
		while (!$new_product->isEmpty()) {

			$senditem = $new_product->shift();
			if (strlen ($senditem) < 1) continue;
			echo "$senditem <br>";
		}
		echo "<h2>Market Share Linked to Existing Product</h2>";
		while (!$linked_sales->isEmpty()) {

			$senditem = $linked_sales->shift();
			if (strlen ($senditem) < 1) continue;
			echo "$senditem <br>";
		}
	} else {
		echo "Unable to read the file";

	}

	fclose($handle);

	print "Import done";

	//view upload form
} else {

	//print "Upload new csv by browsing to file and clicking on Upload<br/>\n";
}

?>
