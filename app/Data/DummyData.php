<?php

namespace App\Data;

class DummyData
{
    protected $regions = [
        ['id' => 1, 'name' => 'Tanger-Tétouan-Al Hoceïma'],
        ['id' => 2, 'name' => 'Oriental'],
        ['id' => 3, 'name' => 'Fès-Meknès'],
        ['id' => 4, 'name' => 'Rabat-Salé-Kénitra'],
        ['id' => 5, 'name' => 'Béni Mellal-Khénifra'],
        ['id' => 6, 'name' => 'Casablanca-Settat'],
        ['id' => 7, 'name' => 'Marrakech-Safi'],
        ['id' => 8, 'name' => 'Drâa-Tafilalet'],
        ['id' => 9, 'name' => 'Souss-Massa'],
        ['id' => 10, 'name' => 'Guelmim-Oued Noun'],
        ['id' => 11, 'name' => 'Laâyoune-Sakia El Hamra'],
        ['id' => 12, 'name' => 'Dakhla-Oued Ed Dahab'],
    ];
    
    protected $cities = [
        ['id' => 1, 'name' => 'Tanger-Assilah', 'region_id' => 1],
        ['id' => 2, 'name' => 'Hoceïma', 'region_id' => 1],
        ['id' => 3, 'name' => 'Oujda-Angad', 'region_id' => 2],
        ['id' => 4, 'name' => 'iteNadorm', 'region_id' => 2],
        ['id' => 5, 'name' => 'Fès', 'region_id' => 3],
        ['id' => 6, 'name' => 'Meknès', 'region_id' => 3],
        ['id' => 7, 'name' => 'Rabat', 'region_id' => 4],
        ['id' => 8, 'name' => 'Salé', 'region_id' => 4],
        ['id' => 9, 'name' => 'Khénifra', 'region_id' => 5],
        ['id' => 10, 'name' => 'Khouribga', 'region_id' => 5],
        ['id' => 11, 'name' => 'Casablanca', 'region_id' => 6],
        ['id' => 12, 'name' => 'Settat', 'region_id' => 6],
        ['id' => 10, 'name' => 'Marrakech', 'region_id' => 7],
        ['id' => 10, 'name' => 'Safi', 'region_id' => 7],
        ['id' => 10, 'name' => 'Ouarzazate', 'region_id' => 8],
        ['id' => 10, 'name' => 'Errachidia', 'region_id' => 8],
        ['id' => 10, 'name' => 'Agadir Ida-Outanane', 'region_id' => 9],
        ['id' => 10, 'name' => 'Taroudant', 'region_id' => 9],
        ['id' => 10, 'name' => 'Guelmim', 'region_id' => 10],
        ['id' => 10, 'name' => 'Assa-Zag', 'region_id' => 10],
        ['id' => 10, 'name' => 'Laâyoune', 'region_id' => 11],
        ['id' => 10, 'name' => 'Tarfaya', 'region_id' => 11],
        ['id' => 10, 'name' => 'Oued Ed Dahab', 'region_id' => 12],
        ['id' => 10, 'name' => 'Aousserd', 'region_id' => 12],
    ];
    
    protected $sectors = [
        ['id' => 1, 'name' => 'Secteur 1'],
        ['id' => 2, 'name' => 'Secteur 2'],
        ['id' => 3, 'name' => 'Secteur 3'],
        ['id' => 4, 'name' => 'Secteur 4'],
    ];
    
    protected $branches = [
        ['id' => 1, 'name' => 'Branche 11', 'sector_id' => 1],
        ['id' => 2, 'name' => 'Branche 12', 'sector_id' => 1],
        ['id' => 3, 'name' => 'Branche 13', 'sector_id' => 1],
        ['id' => 4, 'name' => 'Branche 21', 'sector_id' => 2],
        ['id' => 5, 'name' => 'Branche 22', 'sector_id' => 2],
        ['id' => 6, 'name' => 'Branche 23', 'sector_id' => 2],
        ['id' => 7, 'name' => 'Branche 31', 'sector_id' => 3],
        ['id' => 8, 'name' => 'Branche 32', 'sector_id' => 3],
        ['id' => 9, 'name' => 'Branche 33', 'sector_id' => 3],
        ['id' => 10, 'name' => 'Branche 41', 'sector_id' => 4],
        ['id' => 11, 'name' => 'Branche 42', 'sector_id' => 4],
        ['id' => 12, 'name' => 'Branche 43', 'sector_id' => 4],
    ];
    
    protected $subBranches = [
        ['id' => 1, 'name' => 'Sous-branche 111', 'banch_id' => 1],
        ['id' => 2, 'name' => 'Sous-branche 112', 'banch_id' => 1],
        ['id' => 3, 'name' => 'Sous-branche 121', 'banch_id' => 2],
        ['id' => 4, 'name' => 'Sous-branche 122', 'banch_id' => 2],
        ['id' => 5, 'name' => 'Sous-branche 131', 'banch_id' => 3],
        ['id' => 6, 'name' => 'Sous-branche 132', 'banch_id' => 3],
        ['id' => 7, 'name' => 'Sous-branche 211', 'banch_id' => 4],
        ['id' => 8, 'name' => 'Sous-branche 212', 'banch_id' => 4],
        ['id' => 9, 'name' => 'Sous-branche 221', 'banch_id' => 5],
        ['id' => 10, 'name' => 'Sous-branche 222', 'banch_id' => 5],
        ['id' => 11, 'name' => 'Sous-branche 231', 'banch_id' => 6],
        ['id' => 12, 'name' => 'Sous-branche 232', 'banch_id' => 6],
        ['id' => 13, 'name' => 'Sous-branche 311', 'banch_id' => 7],
        ['id' => 14, 'name' => 'Sous-branche 312', 'banch_id' => 7],
        ['id' => 15, 'name' => 'Sous-branche 321', 'banch_id' => 8],
        ['id' => 16, 'name' => 'Sous-branche 322', 'banch_id' => 8],
        ['id' => 17, 'name' => 'Sous-branche 331', 'banch_id' => 9],
        ['id' => 18, 'name' => 'Sous-branche 332', 'banch_id' => 9],
        ['id' => 19, 'name' => 'Sous-branche 411', 'banch_id' => 10],
        ['id' => 20, 'name' => 'Sous-branche 412', 'banch_id' => 10],
        ['id' => 21, 'name' => 'Sous-branche 421', 'banch_id' => 11],
        ['id' => 22, 'name' => 'Sous-branche 422', 'banch_id' => 11],
        ['id' => 23, 'name' => 'Sous-branche 431', 'banch_id' => 12],
        ['id' => 24, 'name' => 'Sous-branche 432', 'banch_id' => 12],
    ];

    protected function getDataList($array, $key, $value)
    {
        $results = array();

        if (!is_array($array)) return false;

        foreach ($array as $subarray) {
            if($subarray[$key] == $value) {
                $results[] = $subarray;
            }
        }
        return $results;
    }

    public function getRegions()
    {
        return $this->regions;
    }

    public function getCities($value)
    {
        return $this->getDataList($this->cities, 'region_id', $value);
    }

    public function getActivityArea()
    {
        return $this->sectors;
    }

    public function getBranches($value)
    {
        return $this->getDataList($this->branches, 'sector_id', $value);
    }

    public function getSubBranches($value)
    {
        return $this->getDataList($this->subBranches, 'banch_id', $value);
    }
}