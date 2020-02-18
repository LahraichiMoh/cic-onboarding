<?php

namespace App\Services;

use PhpQuery\PhpQuery;

class IceService
{
    protected $ice;
    protected $apiUrl = 'https://maroc.welipro.com/recherche';
    protected $data = [];

    public function __construct($ice, $apiUrl = null) {
        $this->setIce($ice);
        $this->setApiUrl($apiUrl);
    }

    /**
     * Get information of the ICE number
     */
    public function getICEInformations()
    {
        // if(!$this->ice || !$this->apiUrl) return false;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiUrl.'?q='.$this->ice.'&type=&rs=&cp=1&cp_max=2035272260000&et=&v=',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $htmlResponse = curl_exec($curl);

        curl_close($curl);

        $pq=new PhpQuery;
        $pq->load_str($htmlResponse);

        $company = [];
	
        $statusResponse = $pq->query('span.font-weight-semibold')[0]->textContent;

        if($statusResponse !== 'Aucun') {
            // Company name
            $company[] = ['name' => 'Nom de la compagnie', 'value' => trim($pq->query('h3.card-title a')[0]->textContent)];

            // Company activity
            $company[] = ['name' => 'Activité', 'value' => trim(preg_replace('/\s\s+/', ' ', $pq->query('h3.card-title span.d-block.font-size-base')[0]->textContent))];

            // Company description
            // $company['description'] = $pq->query('#result div.card-body')[2];

            // Company ICE
            $company[] = ['name' => 'ICE', 'value' => trim($pq->query('ul.list-group.list-group-flush.border-top li div.ml-auto')[0]->textContent)];

            // Company RC number
            $rcNumber = trim(preg_replace('/\s\s+/', ' ', $pq->query('ul.list-group.list-group-flush.border-top li div.ml-auto')[1]->textContent));
            $rcNumber = preg_replace( '/[^0-9]/', '', $rcNumber);
            $company[] = ['name' => 'Numéro RC', 'value' => $rcNumber];
            

            // Company RC center
            $company[] = ['name' => 'Centre RC', 'value' => trim(preg_replace('/\s\s+/', ' ', $pq->query('ul.list-group.list-group-flush.border-top li div.ml-auto a')[0]->textContent))];

            // Company tax identification
            $company[] = ['name' => 'Identifiant fiscal', 'value' => trim(preg_replace('/\s\s+/', ' ', $pq->query('ul.list-group.list-group-flush.border-top li div.ml-auto')[2]->textContent))];

            // Company creation date
            $company[] = ['name' => 'Date de création', 'value' => trim(preg_replace('/\s\s+/', ' ', $pq->query('ul.list-group.list-group-flush.border-top li div.ml-auto')[3]->textContent))];

            // Company status
            // $company[] = ['name' => 'Statut', 'value' => trim(preg_replace('/\s\s+/', ' ', $pq->query('ul.list-group.list-group-flush.border-top li div.ml-auto')[4]->textContent))];

            // Company address
            $company[] = ['name' => 'Siège social', 'value' => trim(preg_replace('/\s\s+/', ' ', $pq->query('ul.list-group.list-group-flush.border-top li.list-group-item')[5]->textContent))];

            // Company capital
            $company[] = ['name' => 'Capital', 'value' => trim($pq->query('h3.card-title span.d-block.font-size-base span.text-warning')[0]->textContent)];
        }

        return $company;
    }

    /**
     * Set url of the Api used to get ICE informations
     */
    public function setApiUrl($apiUrl) {
        if(!empty($apiUrl)) $this->apiUrl = $apiUrl;
    }

    /**
     * Get url of the Api used to get ICE informations
     */
    public function getApiUrl($apiUrl) { return $this->apiUrl; }

    /**
     * Set ICE number
     */
    public function setIce($ice) {
        $ice = str_replace(' ', '', $ice);
        if(!empty($ice)) $this->ice = $ice;
    }

    /**
     * Get ICE number
     */
    public function getIce($ice) { return $this->ice; }
}