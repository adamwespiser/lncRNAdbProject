<?php
	class lncRNA_DB extends SQLite3 {
	
		public $entropyExprs = array('entropyExpr', 'varianceExpr', 'averageExpr', 'minExpr', 'maxExpr');
		public $cellExprs = array('A549.longNonPolyA', 'A549.longPolyA', 'AG04450.longNonPolyA', 'AG04450.longPolyA', 'BJ.longNonPolyA', 'BJ.longPolyA', 'GM12878.longNonPolyA', 'GM12878.longPolyA', 'H1.HESC.longNonPolyA', 'H1.HESC.longPolyA', 'HELA.S3.longNonPolyA', 'HELA.S3.longPolyA', 'HEPG2.longNonPolyA', 'HEPG2.longPolyA', 'HMEC.longNonPolyA', 'HMEC.longPolyA', 'HSMM.longNonPolyA', 'HSMM.longPolyA', 'HUVEC.longNonPolyA', 'HUVEC.longPolyA', 'K562.longNonPolyA', 'K562.longPolyA', 'MCF.7.longNonPolyA', 'MCF.7.longPolyA', 'NHEK.longNonPolyA', 'NHEK.longNonPolyA.1', 'NHEK.longPolyA', 'NHEK.longPolyA.1', 'NHLF.longNonPolyA', 'NHLF.longPolyA', 'SK.N.SH_RA.longNonPolyA', 'SK.N.SH_RA.longPolyA');

		function __construct() {
			$this->open('/home/wespisea/databaseProject/data/lncRNA.db');
			$this->createFunction('sqrt', 'sqrt');
		}
		
		/*
		 * Using a search query, traverses the database and returns an array of data just as in the database
		 *
		 * @param $sql A search query to be used to get information from the database
		 * @return Returns an array of data, where each index is a row from the results of the database query
		 */
		function getAll($sql) {
			$ret = $this->query($sql);
			$data = array();	
			$row = $ret->fetchArray(SQLITE_ASSOC);
			for ($i = 0; $row; $i++) {
				$data[$i] = $row;
				$row = $ret->fetchArray(SQLITE_ASSOC);
			}
			return $data;
		}
		
		/*
		 * Using a search query, traverses the database and returns a multidimentional array of data, formatted as follows:
		 *	
		 * ------------------------------------------------------------	
		 * | transIDs[0] | transIDs[1] | transIDs[2] |\\| transIDs[n] |	
		 * ------------------------------------------------------------	
		 * 	
		 * Where each transcript ID is an array containing the following:	
		 *	
		 * --------------------------------------------------------------------	
		 * | coordsData | polyData | nonPolyData | entropyData | sumStatsData |	
		 * --------------------------------------------------------------------	
		 * 
		 * Where each element represents an array of data:
		 *	coordsData - chromosome, low, high
		 *	polyData - all cell type expressions of poly A, sorted alphabetically by cell type
		 *	nonPolyData - all cell type expressions of non poly A, sorted alphabetically by cell type
		 *	entropyData - entropyExpr, sumExpr, varianceExpr, averageExpr, minExpr, maxExpr
		 *	sumStatsData - maxExprType, threeTimesRest, tissSpec
		 *
		 * @param $sql A search query to be used to get information from the database
		 * @return Returns an array of information with the above format
		 */

	
		function getAllOrganized($sql) {
			$ret = $this->query($sql);
			$data = array();
			$maxPolyData = array();
			$maxNonPolyData = array();
			$row = $ret->fetchArray(SQLITE_ASSOC);
			while ($row) {
				$coordsData = array();
				$polyData = array();
				$pCounter = 0;
				$nonPolyData = array();
				$npCounter = 0;
				$entropyData = array();
				$sumStatsData = array();
				$transID = $row['transID'];
				
				//Configure coordinates array
				$coordsData["chromosome"] = $row["chromosome"];
				$coordsData["low"] = $row["low"];
				$coordsData["high"] = $row["high"];
				
				//Configure Poly and Non Poly data arrays
				for ($i = 0; $i < count($this->cellExprs); $i++) {
					$curExpr = $this->cellExprs[$i];
					$curDatum = $row[$curExpr];
					if (stristr($curExpr, ".longNonPolyA")) {
						$nonPolyData[$npCounter] = $curDatum;
						if (!isset($maxNonPolyData[$npCounter]) || $curDatum > $maxNonPolyData[$npCounter]) {
							$maxNonPolyData[$npCounter] = $curDatum;
						}
						$npCounter++;
					}	
					else {
						$polyData[$pCounter] = $curDatum;
						if (!isset($maxPolyData[$pCounter]) || $curDatum > $maxPolyData[$pCounter]) {
							$maxPolyData[$pCounter] = $curDatum;
						}
						$pCounter++;
					}
				}
				
				//Configure entropy array
				for ($i = 0; $i < count($this->entropyExprs); $i++) {
					$curExpr = $this->entropyExprs[$i];
					$entropyData[$i] = $row[$curExpr];
				}
					
				//Configure summary statistics data
				$sumStatsData["maxExprType"] = $row["maxExprType"];
				$sumStatsData["threeTimesRest"] = $row["threeTimesRest"];
				$sumStatsData["tissSpec"] = $row["tissSpec"];
				
				$data[$transID] = array("coordsData" => $coordsData, "polyData" => $polyData, "nonPolyData" => $nonPolyData, "entropyData" => $entropyData, "sumStatsData" => $sumStatsData);
				$row = $ret->fetchArray(SQLITE_ASSOC);
			}
			$data["Max"] = array("polyData" => $maxPolyData, "nonPolyData" => $maxNonPolyData);
			return $data;
		}	
	}

	/*
	 * Checks the input to make sure it's in either Gene ID or Gene Coordinate format
	 *
	 * @param $input The input to be checked
	 * @return True if the input if either a Gene ID or Gene Coordinate, false otherwise
	 */
	function checkInput($input) {
		$geneIDMatch = "#^[ENSGRensgr]{1,5}[0-9]{0,12}(\.[0-9]{1,2}|)$#";
		$coordsMatch = "#^chr[0-9XYxy]{1,2}(.[0-9,]{1,15}-[0-9,]{1,15}|)$#";	
		return preg_match($geneIDMatch, $input) | preg_match($coordsMatch, $input);
	}

	/*
	 * Returns the input as an integer without any commas
	 *
	 * @param $value A string consisting of a number with commas
	 * @return The original value as an integer
	 */
	function removeComma($value) {
		$rValue = $value;
		$index = strrpos($rValue, ",");
		while ($index != FALSE) {
			$begin = substr($rValue, 0, $index);
			$end = substr($rValue, $index + 1);
			$rValue = $begin . $end;
			$index = strrpos($rValue, ",");
		}
		return intval($rValue);
	}
			


	$cellTypes = array('A549', 'AG04450', 'BJ', 'GM12878', 'H1.HESC', 'HELA.S3', 'HEPG2', 'HMEC', 'HSMM', 'HUVEC', 'K562', 'MCF.7', 'NHEK', 'NHEK', 'NHLF', 'SK.N.SH_RA');


	$entropyTypes = array('Entropy', 'Variance', 'Average', 'Minimum', 'Maximum');

?>
