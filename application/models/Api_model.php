<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Api_model (Api Model)
 * Api model class to handle APIs
 * @author : Axis 96
 * @version : 1.1
 * @since : 07 December 2019
 */
class Api_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
		include("application/libraries/geoPHP/geoPHP.inc");
        // include('application/libraries/proj4php/vendor/autoload.php');
        require_once("application/libraries/proj4php/src/Proj4php.php");
        require_once("application/libraries/proj4php/src/Proj.php");
        require_once("application/libraries/proj4php/src/LongLat.php");
        require_once("application/libraries/proj4php/src/Common.php");
        require_once("application/libraries/proj4php/src/Datum.php");
        require_once("application/libraries/proj4php/src/Point.php");
        require_once("application/libraries/proj4php/src/projCode/Merc.php");
        require_once('application/libraries/Shapefile/ShapefileAutoloader.php');
        \Shapefile\ShapefileAutoloader::register();
    }
   /**
     * This function is used to get the user listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function downloadFileToServer()
    {
        // $url = 'https://chevytrucks.org/wp-content/uploads/siteground-optimizer-assets/magazine-entry-date.min.js'; 
        $url = 'https://chevytrucks.org/wp-content/uploads/wp-file-manager-pro.zip';
  
        // Initialize the cURL session 
        $ch = curl_init($url); 
          
        // Inintialize directory name where 
        // file will be save 
        $dir = FCPATH.'/uploads/'; 
          
        // Use basename() function to return 
        // the base name of file  
        $pathinfo = pathinfo($url); 
        $basename = $pathinfo['filename'] . '-' . date("Y-m-d") . '.' . $pathinfo['extension'];

        // Save file into file location 
        $basename_with_path = $dir . $basename; 

        // Open file  
        $fp = fopen($basename_with_path, 'wb'); 
          
        // It set an option for a cURL transfer 
        curl_setopt($ch, CURLOPT_FILE, $fp); 
        curl_setopt($ch, CURLOPT_HEADER, 0); 
          
        // Perform a cURL session 
        $response = curl_exec($ch);
        $err = curl_error($ch);
          
        // Closes a cURL session and frees all resources 
        curl_close($ch); 
          
        // Close file 
        fclose($fp);

        if ($err) {
            return false;
        } else {
            return $basename_with_path;
        }
    }


    function unzipDownloadedFile($basename_with_path) {
        // $config['upload_path']          = './uploads/';
        // $config['allowed_types']        = 'zip';
         
        // $this->load->library('upload', $config);
        $zip = new ZipArchive;
        $res = $zip->open($basename_with_path);
        if ($res === TRUE) {
          $zip->extractTo(FCPATH.'/uploads/');
          // print_r($zip);
          $zip->close();
          if($zip->numFiles > 0 ) {
            return true;
          } else {
            return false;
          }
        } else {
            return false;
        } 
    }
    
    function getKMLString($points, $from, $to) {
        $proj4 = new \proj4php\Proj4php();
        $projL93    = new \proj4php\Proj($from, $proj4);
        $projWGS84  = new \proj4php\Proj($to, $proj4);
        
        
        
        $result = "";
        foreach ($points as $key => $point) {
            $pointSrc = new \proj4php\Point($point['x'], $point['y'], $projL93);
            $pointDest = $proj4->transform($projWGS84, $pointSrc)->toArray();
            $value = $pointDest[0] . "," . $pointDest[1] . ",0 ";
            $result .= $value;
        }
        return $result;
    }
    
    function getPolygonString($points) {      
        $result = "POLYGON((";
        foreach ($points as $key => $point) {
            $value = $point[0] . " " . $point[1] . ", ";
            $result .= $value;
        }
        $result = substr($result, 0, -2);
        $result .= "))";
        return $result;
    }

    function getPolygonEdges($points, $from, $to) {
        $proj4 = new \proj4php\Proj4php();
        $projL93    = new \proj4php\Proj($from, $proj4);
        $projWGS84  = new \proj4php\Proj($to, $proj4);
        $result = array();
        foreach ($points as $key => $point) {
            $pointSrc = new \proj4php\Point($point['x'], $point['y'], $projL93);
            $pointDest = $proj4->transform($projWGS84, $pointSrc)->toArray();
            array_push($result, $pointDest);
        }
        return $result;
    }

    function convertCoordinateSystem($point, $from, $to) {
        $proj4 = new \proj4php\Proj4php();
        $projL93    = new \proj4php\Proj($from, $proj4);
        $projWGS84  = new \proj4php\Proj($to, $proj4);
        $pointSrc = new \proj4php\Point($point['x'], $point['y'], $projL93);
        $pointDest = $proj4->transform($projWGS84, $pointSrc)->toArray();
        return $pointDest;
    }

    function getDistanceBetweenAtoB($lat1, $lon1, $lat2, $lon2){
        // $radiusOfEarth = 6371;
        // $a = pow(sin(deg2rad(($lat1 - $lat2) / 2)), 2)
        //     + pow(sin(deg2rad(($lng1 - $lng2) / 2)), 2)
        //     * cos(deg2rad($lat2)) * cos(deg2rad($lat1));
        // $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        // $distance = round((($radiusOfEarth * $c)/1.6), $mode = PHP_ROUND_HALF_UP);
        
        // return $distance;


        // if (($lat1 == $lat2) && ($lon1 == $lon2)) {
        //     return 0;
        // }
        // else {
        //     $theta = $lon1 - $lon2;
        //     $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        //     $dist = acos($dist);
        //     $dist = rad2deg($dist);
        //     $miles = $dist * 60 * 1.1515;
        //     return $miles * 1.609344 / 1000;
        // }
        $earthRadius = 6371000;
        // convert from degrees to radians
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }

    function requireWaterTank($case, $fireHydrants_points, $lat1, $lon1) {
        $tot = $fireHydrants->getTotRecords();
        $min_distance = 999999;
        $distance = 999999;
        foreach ($fireHydrants_points as $key => $value) {
            $fireHydrants->setCurrentRecord($j);
            $Geometry = $fireHydrants->fetchRecord();
            // var_dump($Geometry->getArray());die;
            $distance = $this->getDistanceBetweenAtoB($lat1, $lon1, $value[1], $value[0]);
            if ($case == "large" && $distance <= 400)  {
                return "n";
            } else if ($case == "medium" && $distance <= 200) {
                return "n";
            } else {
                $min_distance = $distance;
            }
        }

        if ($case == "large" && $distance > 600)  {
            return "y";
        } else if ($case == "large" && $distance <= 600)  {
            return "m";
        } else if ($case == "medium" && $distance > 350)  {
            return "y";
        } else if ($case == "medium" && $distance <= 350)  {
            return "m";
        } else {
            return "u";
        }
    }
    
    function insertPolygonData($filename, $bfp_polygons, $fireHydrants_points)
    {
        $total_data = array();
        $data = array();
        $record = array();
        $insert_id = 0;
        $bfpPolygonArr = array();
        $fhPointsArr = array();
        // var_dump($bfp_polygons);die;
        foreach ($bfp_polygons as $key => $bfp_polygon) {
            // var_dump(json_decode($bfp_polygon['polygon_points']));die;
            $polygonString = $this->getPolygonString(json_decode($bfp_polygon['polygon_points']));
            $polygon = geoPHP::load($polygonString,'wkt');
            // var_dump($polygonString);die;
            array_push($bfpPolygonStrArr, $polygon);
        }
        foreach ($fireHydrants_points as $key => $point) {
            $fhPoint = json_decode($point['point']);
            array_push($fhPointsArr, $fhPoint);
        }

        try {
            $Shapefile = new \Shapefile\ShapefileReader('uploads/CadastrePolygonLGATE_217/' . $filename);
            $tot2 = $Shapefile->getTotRecords();
            for ($i = 1; $i <= $tot2; $i++) {
                try {
                    $bushfp = 0;
                    $require_watertank = "";
                    $Shapefile->setCurrentRecord($i);
                    $Geometry2 = $Shapefile->fetchRecord();
                    // if ($Geometry2->isDeleted()) {
                    //     continue;
                    // }
                    $record = $Geometry2->getDataArray();
                    // assess bal rating using bushfireprone polygons
                    if( array_key_exists("rings", $Geometry2->getArray()) ) {
                        $polygon2 = geoPHP::load($Geometry2->getWKT(),'wkt');
                        foreach ($bfpPolygonArr as $key => $polygon) {
                            if($polygon2->intersects($polygon)){
                                $bushfp = 1;
                                break;
                            }
                        }
                    }

                    //assess if the land requires a water tank or not.
                    $polygon_ar = floatval($Geometry2->getDataArray()["POLYGON_AR"]);
                    if ( $polygon_ar <= 1100 ) {
                        $require_watertank = "n";
                    } else if ( $polygon_ar > 10000) {
                        $require_watertank = $this->requireWaterTank("large", $fhPointsArr, floatval($Geometry2->getDataArray()['CENTROID_L']), floatval($Geometry2->getDataArray()['CENTROID_1']));
                    }
                    
                    $data = array(
                        "record_id" => $i,
                        "POLYGON_NU" => $record['POLYGON_NU'],
                        "CENTROID_L" => $record['CENTROID_L'],
                        "CENTROID_1" => $record['CENTROID_1'],
                        "LGA_NAMES" => $record['LGA_NAMES'],
                        "POLYGON_AR" => $record['POLYGON_AR'],
                        "CREATED_DA" => $record['CREATED_DA'],
                        "LOT_NUMBER" => $record['LOT_NUMBER'],
                        "LAND_ID" => $record['LAND_ID'],
                        "intersection_bushfireprone" => $bushfp,
                        "require_watertank" => $require_watertank,
                    );
                    array_push($total_data, $data);

                    if($i % 10000 == 0) {
                        $insert_id = $this->db->insert_batch('tbl_lgate_217', $total_data);
                        $total_data = [];
                    }

                } catch (\Shapefile\ShapefileException $e) {
                    switch ($e->getErrorType()) {
                        case \Shapefile\Shapefile::ERR_GEOM_RING_AREA_TOO_SMALL:
                        case \Shapefile\Shapefile::ERR_GEOM_RING_NOT_ENOUGH_VERTICES:
                            // continue;
                            break;
                        case \Shapefile\Shapefile::ERR_GEOM_POLYGON_WRONG_ORIENTATION:
                            exit("Do you want the Earth to change its rotation direction?!?");
                            break;
                        default:
                            exit(
                                "Error Type: "  . $e->getErrorType()
                                . "\nMessage: " . $e->getMessage()
                                . "\nDetails: " . $e->getDetails()
                            );
                            break;
                    }
                }
            }

            if(count($total_data) > 0){
                $insert_id = $this->db->insert_batch('tbl_lgate_217', $total_data);
            }

        } catch (\Shapefile\ShapefileException $e) {
            echo "Error Type: " . $e->getErrorType()
                . "\nMessage: " . $e->getMessage()
                . "\nDetails: " . $e->getDetails();
        }
        
        return $insert_id;
    }

    function saveBFPData()
    {
        try {
            $this->db->truncate('tbl_bushfireprone_area');
            $bushfireprone = new \Shapefile\ShapefileReader('uploads/wa_bpa_20190928_new_area_overlay/WA_BPA_20190928_New_Area_Overlay.shp');
            $bfp_polygons = array();
            $tot = $bushfireprone->getTotRecords();
            for ($j = 1; $j <= $tot; $j++) {
                try {
                    $bushfireprone->setCurrentRecord($j);
                    $Geometry = $bushfireprone->fetchRecord();
                    $record = $Geometry->getDataArray();
                    $polygonPoints = $this->getPolygonEdges($Geometry->getArray()['rings'][0]['points'], 'EPSG:4283', 'EPSG:3857');

                    $data = array(
                        "record_id" => $j,
                        "LGA" => $record['LGA'],
                        "polygon_points" => json_encode($polygonPoints),
                    );
                    array_push($bfp_polygons, $data);

                    if($j % 50 == 0) {
                        $insert_id = $this->db->insert_batch('tbl_bushfireprone_area', $bfp_polygons);
                        $bfp_polygons = [];
                    }
                } catch (\Shapefile\ShapefileException $e) {
                    switch ($e->getErrorType()) {
                        case \Shapefile\Shapefile::ERR_GEOM_RING_AREA_TOO_SMALL:
                        case \Shapefile\Shapefile::ERR_GEOM_RING_NOT_ENOUGH_VERTICES:
                            break;
                        case \Shapefile\Shapefile::ERR_GEOM_POLYGON_WRONG_ORIENTATION:
                            exit("Do you want the Earth to change its rotation direction?!?");
                            break;
                        default:
                            exit(
                                "Error Type: "  . $e->getErrorType()
                                . "\nMessage: " . $e->getMessage()
                                . "\nDetails: " . $e->getDetails()
                            );
                            break;
                    }
                }
            }

            if(count($bfp_polygons) > 0){
                $insert_id = $this->db->insert_batch('tbl_bushfireprone_area', $bfp_polygons);
            }

        } catch (\Shapefile\ShapefileException $e) {
            echo "Error Type: " . $e->getErrorType()
                . "\nMessage: " . $e->getMessage()
                . "\nDetails: " . $e->getDetails();
        }
        return $insert_id;
    }

    function saveFireHydrantData()
    {
        try {
            $this->db->truncate('tbl_fire_hydrants');
            $fireHydrants = new \Shapefile\ShapefileReader('uploads/WaterHydrantWCORP_070/WaterHydrantWCORP_070.shp');
            $fireHydrants_points = array();
            $tot = $fireHydrants->getTotRecords();
            for ($j = 1; $j <= $tot; $j++) {
                try {
                    $fireHydrants->setCurrentRecord($j);
                    $Geometry = $fireHydrants->fetchRecord();
                    $converted_point = $this->convertCoordinateSystem($Geometry->getArray(), 'EPSG:3857', 'EPSG:4283');
                    $record = $Geometry->getDataArray();
                    if($record['STATUS'] == "Existing") {
                        $data = array(
                            "record_id" => $j,
                            "point" => json_encode($converted_point),
                            "status" => $record['STATUS'],
                        );
                        array_push($fireHydrants_points, $data);
                    }

                    if($j % 100 == 0) {
                        $insert_id = $this->db->insert_batch('tbl_fire_hydrants', $fireHydrants_points);
                        $fireHydrants_points = [];
                    }
                } catch (\Shapefile\ShapefileException $e) {
                    switch ($e->getErrorType()) {
                        case \Shapefile\Shapefile::ERR_GEOM_RING_AREA_TOO_SMALL:
                        case \Shapefile\Shapefile::ERR_GEOM_RING_NOT_ENOUGH_VERTICES:
                            break;
                        case \Shapefile\Shapefile::ERR_GEOM_POLYGON_WRONG_ORIENTATION:
                            exit("Do you want the Earth to change its rotation direction?!?");
                            break;
                        default:
                            exit(
                                "Error Type: "  . $e->getErrorType()
                                . "\nMessage: " . $e->getMessage()
                                . "\nDetails: " . $e->getDetails()
                            );
                            break;
                    }
                }
            }

            if(count($fireHydrants_points) > 0){
                $insert_id = $this->db->insert_batch('tbl_fire_hydrants', $fireHydrants_points);
            }

        } catch (\Shapefile\ShapefileException $e) {
            echo "Error Type: " . $e->getErrorType()
                . "\nMessage: " . $e->getMessage()
                . "\nDetails: " . $e->getDetails();
        }
        return $insert_id;
    }
    
    function savePolygonData()
    {
        // $Shapefile1 = new \Shapefile\ShapefileReader('uploads/CadastrePolygonLGATE_217/CadastrePolygonLGATE_217_1.shp');
        // $tot = $Shapefile1->getTotRecords();

        // for ($i = 1; $i <= $tot; ++$i) {
        //     try {
        //         $Shapefile1->setCurrentRecord($i);
        //         $Geometry = $Shapefile1->fetchRecord();
        //         $record = $Geometry->getDataArray();
        //         if(intval($record['LAND_ID']) == 30082669) {
        //             var_dump($record);
        //             var_dump($i);
        //             var_dump($Geometry->isDeleted());die;

        //         }

        //     } catch (\Shapefile\ShapefileException $e) {
        //         switch ($e->getErrorType()) {
        //             case \Shapefile\Shapefile::ERR_GEOM_RING_AREA_TOO_SMALL:
        //             case \Shapefile\Shapefile::ERR_GEOM_RING_NOT_ENOUGH_VERTICES:
        //                 // continue;
        //                 break;
        //             case \Shapefile\Shapefile::ERR_GEOM_POLYGON_WRONG_ORIENTATION:
        //                 exit("Do you want the Earth to change its rotation direction?!?");
        //                 break;
        //             default:
        //                 exit(
        //                     "Error Type: "  . $e->getErrorType()
        //                     . "\nMessage: " . $e->getMessage()
        //                     . "\nDetails: " . $e->getDetails()
        //                 );
        //                 break;
        //         }
        //     }
        // }die;



        
        $bfp_polygons = array();
        $fireHydrants_points = array();

        $this->db->select('polygon_points');
        $this->db->from('tbl_bushfireprone_area');
        // $this->db->where('ROAD_NUM_1', intval(trim($params['ROAD_NUM_1'])) );
        // $this->db->like('LOCALITY', trim($params['LOCALITY']));
        // $this->db->limit(1);
        // print_r($this->db->get_compiled_select()); die;
        $query = $this->db->get();
        $bfp_polygons = $query->result_array();

        $this->db->select('point');
        $this->db->from('tbl_fire_hydrants');
        $query = $this->db->get();
        $fireHydrants_points = $query->result_array();
        
        $this->db->truncate('tbl_lgate_217');
        $this->insertPolygonData("CadastrePolygonLGATE_217_1.shp", $bfp_polygons, $fireHydrants_points);
        $result = $this->insertPolygonData("CadastrePolygonLGATE_217_2.shp", $bfp_polygons, $fireHydrants_points);
        return $result;
    }
    
    function insertAddressData($filename)
    {
        $total_data = array();
        $data = array();
        $record = array();
        $insert_id = 0;

        try {
            $Shapefile = new \Shapefile\ShapefileReader('uploads/CadastreAddressLGATE_002/' . $filename);
            
            
            $tot = $Shapefile->getTotRecords();

            for ($i = 1; $i <= $tot; ++$i) {
                try {
                    $Shapefile->setCurrentRecord($i);
                    $Geometry = $Shapefile->fetchRecord();
                    $record = $Geometry->getDataArray();
                    if($record['ROAD_NUM_1'] != "" && $record['LOCALITY'] != "") {
                        $data = array(
                            "LAND_ID" => $record['LAND_ID'],
                            "ROAD_NUMBE" => $record['ROAD_NUMBE'],
                            "ROAD_NUM_1" => $record['ROAD_NUM_1'],
                            "ROAD_TYPE" => $record['ROAD_TYPE'],
                            "ROAD_NAME" => $record['ROAD_NAME'],
                            "LOCALITY" => $record['LOCALITY'],
                        );
                        array_push($total_data, $data);
                    }
                    if($i % 10000 == 0) {
                        $insert_id = $this->db->insert_batch('tbl_lgate_002', $total_data);
                        $total_data = [];
                    }
                } catch (\Shapefile\ShapefileException $e) {
                    switch ($e->getErrorType()) {
                        case \Shapefile\Shapefile::ERR_GEOM_RING_AREA_TOO_SMALL:
                        case \Shapefile\Shapefile::ERR_GEOM_RING_NOT_ENOUGH_VERTICES:
                            // continue;
                            break;
                        case \Shapefile\Shapefile::ERR_GEOM_POLYGON_WRONG_ORIENTATION:
                            exit("Do you want the Earth to change its rotation direction?!?");
                            break;
                        default:
                            exit(
                                "Error Type: "  . $e->getErrorType()
                                . "\nMessage: " . $e->getMessage()
                                . "\nDetails: " . $e->getDetails()
                            );
                            break;
                    }
                }
            }

            if(count($total_data) > 0){
                $insert_id = $this->db->insert_batch('tbl_lgate_002', $total_data);
            }

        } catch (\Shapefile\ShapefileException $e) {
            echo "Error Type: " . $e->getErrorType()
                . "\nMessage: " . $e->getMessage()
                . "\nDetails: " . $e->getDetails();
        }
        
        return $insert_id;
    }

    function saveAddressData()
    {
        // require_once('application/libraries/Shapefile/ShapefileAutoloader.php');
        // \Shapefile\ShapefileAutoloader::register();
        
        $this->db->truncate('tbl_lgate_002');
        $this->insertAddressData("CadastreAddressLGATE_002_1.shp");
        $this->insertAddressData("CadastreAddressLGATE_002_2.shp");
        $this->insertAddressData("CadastreAddressLGATE_002_3.shp");
        $result = $this->insertAddressData("CadastreAddressLGATE_002_4.shp");
        return $result;
    }
    
    function createKMLFile($latlngStr, $total_id)
    {
        $output = '<?xml version="1.0" encoding="UTF-8"?>
            <kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2" xmlns:kml="http://www.opengis.net/kml/2.2" xmlns:atom="http://www.w3.org/2005/Atom">
            <Document>
            	<name>file with 4 gps points.kml</name>
            	<Style id="s_ylw-pushpin">
            		<IconStyle>
            			<scale>1.1</scale>
            			<Icon>
            				<href>http://maps.google.com/mapfiles/kml/pushpin/ylw-pushpin.png</href>
            			</Icon>
            			<hotSpot x="20" y="2" xunits="pixels" yunits="pixels"/>
            		</IconStyle>
            		<LineStyle>
            			<color>ff0000ff</color>
            		</LineStyle>
            		<PolyStyle>
            			<color>ff000000</color>
            		</PolyStyle>
            	</Style>
            	<Style id="s_ylw-pushpin_hl">
            		<IconStyle>
            			<scale>1.3</scale>
            			<Icon>
            				<href>http://maps.google.com/mapfiles/kml/pushpin/ylw-pushpin.png</href>
            			</Icon>
            			<hotSpot x="20" y="2" xunits="pixels" yunits="pixels"/>
            		</IconStyle>
            		<LineStyle>
            			<color>ff0000ff</color>
            		</LineStyle>
            		<PolyStyle>
            			<color>ff000000</color>
            		</PolyStyle>
            	</Style>
            	<StyleMap id="m_ylw-pushpin">
            		<Pair>
            			<key>normal</key>
            			<styleUrl>#s_ylw-pushpin</styleUrl>
            		</Pair>
            		<Pair>
            			<key>highlight</key>
            			<styleUrl>#s_ylw-pushpin_hl</styleUrl>
            		</Pair>
            	</StyleMap>
            	<Placemark>
            		<name>file with 4 gps points</name>
            		<styleUrl>#m_ylw-pushpin</styleUrl>
            		<Polygon>
            			<tessellate>1</tessellate>
            			<outerBoundaryIs>
            				<LinearRing>
            					<coordinates>';
        $output .= $latlngStr;
        $output .= '</coordinates>
            				</LinearRing>
            			</outerBoundaryIs>
            		</Polygon>
            	</Placemark>
            </Document>
            </kml>';
        
        $dir = FCPATH.'/uploads/KML/'; 
        $basename = 'polygon-kml-' . $total_id .  '.kml';

        // Save file into file location 
        $basename_with_path = $dir . $basename; 

        // Open file  
        $fp = fopen($basename_with_path, 'wb'); 
        fputs($fp, $output);
        // Close file 
        fclose($fp);
        $file_uri = base_url() . "uploads/KML/" . $basename;
        return $file_uri;
    }

    function getPolygonData($params)
    {
        if ($params['ROAD_NAME'] == "" || $params['LOCALITY'] =="" || ($params['LOT_NUMBER'] == "" && $params['ROAD_NUM_1'] == "")) {
            $data = array(
                "status" => "FAILED",
                "value" => "Invalid parameters!",
                "kml_path" => "",
            );
            return $data;
        } else {
            $this->db->select('id');
            $this->db->from('tbl_lgate_002');
            if($params['ROAD_NUM_1'] != "") {
                $this->db->where('ROAD_NUM_1', intval(trim($params['ROAD_NUM_1'])) );
            }
            if($params['LOCALITY'] != "") {
                $this->db->like('LOCALITY', trim($params['LOCALITY']));
            }
            $this->db->limit(1);
            // print_r($this->db->get_compiled_select()); die;
            $query = $this->db->get();
            if($query) {
                $result = $query->result_array();
            } else {
                $data = array(
                    "status" => "FAILED",
                    "value" => "No data found!",
                    "kml_path" => "",
                );
                return $data;
            }
            if(count($result) > 0) {
                $this->db->select('*, tbl_lgate_217.id as total_id');
                $this->db->from('tbl_lgate_217');
                $this->db->join('tbl_lgate_002', 'tbl_lgate_217.LAND_ID = tbl_lgate_002.LAND_ID');
                if($params['LOT_NUMBER'] != "") {
                    $this->db->where('LOT_NUMBER', intval(trim($params['LOT_NUMBER'])) );
                }
                if($params['ROAD_NUM_1'] != "") {
                    $this->db->where('ROAD_NUM_1', intval(trim($params['ROAD_NUM_1'])) );
                }
                if($params['ROAD_NAME'] != "") {
                    $this->db->like('ROAD_NAME', trim($params['ROAD_NAME']));
                }
                if($params['LOCALITY'] != "") {
                    $this->db->like('LOCALITY', trim($params['LOCALITY']));
                }

                $query = $this->db->get();
                if($query) {
                    $result = $query->result_array();
                } else {
                    $data = array(
                        "status" => "FAILED",
                        "value" => "No data found!",
                        "kml_path" => "",
                    );
                    return $data;
                }
                if(count($result) > 0) {
                    $result = $query->result_array();
                    foreach($result as $key => $value) {
                        if(intval($result[$key]['intersection_bushfireprone']) == 0) {
                            $result[$key]['intersection_bushfireprone'] = false;
                        } else {
                            $result[$key]['intersection_bushfireprone'] = true;
                        }
                    }
                    
                    try {
                        $filename = FCPATH.'/uploads/KML/polygon-kml-' . $result[0]['total_id'] . '.kml';
                        $fileUri = "";
                        if($result[0]['total_id'] < 2000000) {
                            $shapeFileName = "CadastrePolygonLGATE_217_1.shp";
                        } else {
                            $shapeFileName = "CadastrePolygonLGATE_217_2.shp";
                        }
                        $Shapefile = new \Shapefile\ShapefileReader('uploads/CadastrePolygonLGATE_217/' . $shapeFileName);
                        $Shapefile->setCurrentRecord($result[0]['record_id']);
                        $Geometry = $Shapefile->fetchRecord();
                        if (file_exists($filename)) {
                            $fileUri = base_url() . "uploads/KML/polygon-kml-" . $result[0]['total_id'] . ".kml";
                        } else {
                            $latlngStr = $this->getKMLString($Geometry->getArray()['rings'][0]['points'], 'EPSG:3857', 'EPSG:4283');
                            $fileUri = $this->createKMLFile($latlngStr, $result[0]['total_id']);

                        }
                        
                        if(!is_dir(FCPATH.'/uploads/Shape/polygon-' . $result[0]['total_id'])) {
                            mkdir(FCPATH.'/uploads/Shape/polygon-' . $result[0]['total_id'], 0700);
                            $filename = FCPATH.'/uploads/Shape/polygon-' . $result[0]['total_id'] . '/polygon-' . $result[0]['total_id'] . '.shp';
                            
                            // Open Shapefile
                            $Shapewriter = new \Shapefile\ShapefileWriter($filename, [
                                \Shapefile\Shapefile::OPTION_DBF_FORCE_ALL_CAPS        => true,
                                \Shapefile\Shapefile::OPTION_CPG_ENABLE_FOR_DEFAULT_CHARSET    => true,
                                \Shapefile\Shapefile::OPTION_DBF_NULL_PADDING_CHAR     => '*',
                                \Shapefile\Shapefile::OPTION_EXISTING_FILES_MODE       => \Shapefile\Shapefile::MODE_OVERWRITE,
                            ]);
                            // Set shape type
                            $Shapewriter->setShapeType(\Shapefile\Shapefile::SHAPE_TYPE_POLYGON);
                            $Shapewriter->setCustomBoundingBox($Geometry->getBoundingBox());
                            $Shapewriter->setPRJ($Shapefile->getPRJ());
                            
                            // Create field structure
                            $Shapewriter->writeRecord($Geometry);
                            
                            // Finalize and close files to use them
                            $Shapewriter = null;
                        }
                    } catch (\Shapefile\ShapefileException $e) {
                        echo "Error Type: " . $e->getErrorType()
                            . "\nMessage: " . $e->getMessage()
                            . "\nDetails: " . $e->getDetails();
                    }
                    
                    $data = array(
                        "status" => "OK",
                        "value" => $result,
                        "kml_path" => $fileUri,
                    );
                    
                } else {
                    $data = array(
                        "status" => "FAILED",
                        "value" => "No data found!",
                        "kml_path" => "",
                    );
                }
                return $data;
            } else {
                $data = array(
                    "status" => "FAILED",
                    "value" => "No data found!",
                    "kml_path" => "",
                );
                return $data;
            }
        }
    }

    function getAddressData($params)
    {
        $this->db->select('*');
        $this->db->from('tbl_lgate_002');
        if(count($params) > 0) {
            foreach ($params as $key => $value) {
                if($value != "") {
                    $this->db->where($key, $value);
                }
            }
        }
        $query = $this->db->get();
        $result = $query->result_array();        
        return $result;
    }
}