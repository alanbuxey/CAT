<?php

/*
 * *****************************************************************************
 * Contributions to this work were made on behalf of the GÉANT project, a 
 * project that has received funding from the European Union’s Framework 
 * Programme 7 under Grant Agreements No. 238875 (GN3) and No. 605243 (GN3plus),
 * Horizon 2020 research and innovation programme under Grant Agreements No. 
 * 691567 (GN4-1) and No. 731122 (GN4-2).
 * On behalf of the aforementioned projects, GEANT Association is the sole owner
 * of the copyright in all material which was developed by a member of the GÉANT
 * project. GÉANT Vereniging (Association) is registered with the Chamber of 
 * Commerce in Amsterdam with registration number 40535155 and operates in the 
 * UK as a branch of GÉANT Vereniging.
 * 
 * Registered office: Hoekenrode 3, 1102BR Amsterdam, The Netherlands. 
 * UK branch address: City House, 126-130 Hills Road, Cambridge CB2 1PQ, UK
 *
 * License: see the web/copyright.inc.php file in the file structure or
 *          <base_url>/copyright.php after deploying the software
 */

/**
 * This file contains the ExternalEduroamDBData class. It contains methods for
 * querying the external database.
 *
 * @author Stefan Winter <stefan.winter@restena.lu>
 *
 * @package Developer
 *
 */

namespace core;

use \Exception;

/**
 * This class interacts with the external DB to fetch operational data for
 * read-only purposes.
 * 
 * @author Stefan Winter <stefan.winter@restena.lu>
 *
 * @license see LICENSE file in root directory
 *
 * @package Developer
 */
class ExternalEduroamDBData extends common\Entity implements ExternalLinkInterface {

    /**
     * List of all service providers. Fetched only once by allServiceProviders()
     * and then stored in this property for efficiency
     * 
     * @var array
     */
    private $SPList = [];

    /**
     * our handle to the DB
     * 
     * @var DBConnection
     */
    private $db;

    /**
     * constructor, gives us access to the DB handle we need for queries
     */
    public function __construct() {
        parent::__construct();
        $this->db = DBConnection::handle("EXTERNAL");
        $this->db->exec("SET NAMES 'latin1'");
    }

    /**
     * eduroam DB delivers a string with all name variants mangled in one. Pry
     * it apart.
     * 
     * @param string $nameRaw the string with all name variants coerced into one
     * @return array language/name pair
     * @throws Exception
     */
    private function splitNames($nameRaw) {
        $variants = explode('#', $nameRaw);
        $submatches = [];
        $returnArray = [];
        foreach ($variants as $oneVariant) {
            if ($oneVariant == NULL) {
                continue;
            }
            if (!preg_match('/^(..):\ (.*)/', $oneVariant, $submatches) || !isset($submatches[2])) {
                $this->loggerInstance->debug(2, "[$nameRaw] We expect 'xx: bla but found '$oneVariant'.");
                continue;
            }
            $returnArray[$submatches[1]] = $submatches[2];
        }
        return $returnArray;
    }

    /**
     * retrieves the list of all service providers from the eduroam database
     * 
     * @return array list of providers
     */
    public function allServiceProviders() {
        if (count($this->SPList) == 0) {
            $query = $this->db->exec("SELECT country, inst_name, sp_location FROM view_active_SP_location_eduroamdb");
            while ($iterator = mysqli_fetch_object(/** @scrutinizer ignore-type */ $query)) {
                $this->SPList[] = ["country" => $iterator->country, "instnames" => $this->splitNames($iterator->inst_name), "locnames" => $this->splitNames($iterator->sp_location)];
            }
        }
        return $this->SPList;
    }

    public const TYPE_IDPSP = "1";
    public const TYPE_SP = "2";
    public const TYPE_IDP = "3";
    private const TYPE_MAPPING = [
        IdP::TYPE_IDP => ExternalEduroamDBData::TYPE_IDP,
        IdP::TYPE_IDPSP => ExternalEduroamDBData::TYPE_IDPSP,
        IdP::TYPE_SP => ExternalEduroamDBData::TYPE_SP,
    ];

    /**
     * retrieves entity information from the eduroam database. Choose whether to get all entities with an SP role, an IdP role, or only those with both roles
     * 
     * @param string      $tld  the top-level domain from which to fetch the entities
     * @param string|NULL $type type of entity to retrieve
     * @return array list of entities
     */
    public function listExternalEntities($tld, $type) {
        if ($type === NULL) {
            $eduroamDbType = NULL;
        } else {
            $eduroamDbType = self::TYPE_MAPPING[$type]; // anything
        }
        $returnarray = [];
        $query = "SELECT id_institution AS id, country, inst_realm as realmlist, name AS collapsed_name, contact AS collapsed_contact, type FROM view_active_institution WHERE country = ?";
        if ($eduroamDbType !== NULL) {
            $query .= " AND ( type = '" . ExternalEduroamDBData::TYPE_IDPSP . "' OR type = '" . $eduroamDbType . "')";
        }
        $externals = $this->db->exec($query, "s", $tld);
        // was a SELECT query, so a resource and not a boolean
        while ($externalQuery = mysqli_fetch_object(/** @scrutinizer ignore-type */ $externals)) {
            $names = $this->splitNames($externalQuery->collapsed_name);
            $thelanguage = $names[$this->languageInstance->getLang()] ?? $names["en"] ?? array_shift($names);
            $contacts = explode('#', $externalQuery->collapsed_contact);
            $mailnames = "";
            foreach ($contacts as $contact) {
                $matches = [];
                preg_match("/^n: (.*), e: (.*), p: .*$/", $contact, $matches);
                if ($matches[2] != "") {
                    if ($mailnames != "") {
                        $mailnames .= ", ";
                    }
                    // extracting real names is nice, but the <> notation
                    // really gets screwed up on POSTs and HTML safety
                    // so better not do this; use only mail addresses
                    $mailnames .= $matches[2];
                }
            }
            $convertedType = array_search($externalQuery->type, self::TYPE_MAPPING);
            $returnarray[] = ["ID" => $externalQuery->id, "name" => $thelanguage, "contactlist" => $mailnames, "country" => $externalQuery->country, "realmlist" => $externalQuery->realmlist, "type" => $convertedType];
        }
        usort($returnarray, array($this, "usortInstitution"));
        return $returnarray;
    }

    /**
     * helper function to sort institutions by their name
     * 
     * @param array $a an array with institution a's information
     * @param array $b an array with institution b's information
     * @return int the comparison result
     */
    private function usortInstitution($a, $b) {
        return strcasecmp($a["name"], $b["name"]);
    }

}
