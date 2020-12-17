<?php

use Symfony\Component\HttpClient\HttpClient;

class NovaApi
{
    public static function getApiToken(array $nova_api_env)
    {
        $output = shell_exec('curl -k -d "grant_type=client_credentials&scope=' . $nova_api_env['scope'] . '" -H "Authorization: Basic ' . base64_encode($nova_api_env['consumer_key'] . ":" . $nova_api_env['consumer_secret']) . '" ' . $nova_api_env['endpoint'] . 'api/token');
        $exp = explode('"', $output);
        return $exp[3];
    }

    public static function getApiDocsList(array $nova_api_env, ?array $id_list, string $type = "ID", ?string $jwt_key = null)
    {
        $content = [
            'auth_bearer' => self::getApiToken($nova_api_env),
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],

            'body' => self::getReferencesJson($id_list, $type),
        ];

        if (null != $jwt_key) {
            $content['headers']['x-jwt-api-key'] = $jwt_key;
        }
        $httpClient = HttpClient::create();
        $response = $httpClient->request('POST', $nova_api_env['endpoint'] . 'api/nova-api/document/1.0.0/list/', $content);

        try {
            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);
            if ($statusCode == 200 && $content) {
                $content = $response->toArray();
            };
        } catch (TransportExceptionInterface $e) {
            var_dump($e->getMessage());
        }

        if(!isset($content['publications'])) {
            return [];
        }

        // add origin to each doc
        foreach($content['publications'] as $key => &$val){
           $val['src'] = 'novaapi';
        }
        return $content['publications'] ?? [];
    }

    public static function getApiDocDownload(array $nova_api_env, string $identifier, ?string $jwt_key = null)
    {
        $content = [
            'auth_bearer' => self::getApiToken($nova_api_env),
            'headers' => [
                'Content-Type' => 'application/octet-stream',
            ]
        ];

        if (null != $jwt_key) {
            $content['headers']['x-jwt-api-key'] = $jwt_key;
        }

        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', $nova_api_env['endpoint'] . 'api/nova-api/document/1.0.0/download/identifier/UUID/' . $identifier, $content);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        return $content ?? null;
    }


    public static function getChargesUrbanisme(array $nova_api_env, string $uuid, string $type = "UUID")
    {
        $httpClient = HttpClient::create();
        $response = $httpClient->request('POST', $nova_api_env['endpoint'].'api/nova-api/graph/1.0.0/graphql', [
            'auth_bearer' => self::getApiToken($nova_api_env),
            'headers' => [
                'Content-Type' => 'application/json',
            ],

            'body' => '{
 "query": "query ($arg:IdentifiersInput!) { Case_Details (identifiers:$arg) { urbanCharge { procedure {urbanCharge,cashCharge,natureCharge,imposedCharge,bankGuaranteeRequested}, generatorFact{cobat,governmentOrder{office{active,squareMeter,squareMeterPrice,squareMeterDiscount,squareMeterTheoreticalTotal},productionActivity{active,squareMeter,squareMeterPrice,squareMeterDiscount,squareMeterTheoreticalTotal},parliamentAndService{active,squareMeter,squareMeterPrice,squareMeterDiscount,squareMeterTheoreticalTotal},commercialParking{active,squareMeter,squareMeterPrice,squareMeterDiscount,squareMeterTheoreticalTotal},shop{active,squareMeter,squareMeterPrice,squareMeterDiscount,squareMeterTheoreticalTotal},wholesaler{active,squareMeter,squareMeterPrice,squareMeterDiscount,squareMeterTheoreticalTotal},hotel{active,squareMeter,squareMeterPrice,squareMeterDiscount,squareMeterTheoreticalTotal},housing{active,squareMeter,squareMeterPrice,squareMeterDiscount,squareMeterTheoreticalTotal}},ppas,ppasDetail,ppasTotal,municipalityRule,municipalityRuleDetail,municipalityRuleTotal,subdivisionPermit,subdivisionPermitDetail,subdivisionPermitTotal other,otherDetail,otherTotal,reductionMotivation,reductionMotivationDetail,reductionMotivationTotal,commentDetail,linkedCaseDetail}, natureAndAssignment{nature{cash,nature,freeGrant},assignment{housing,housingTotal,supervisedHousing,supervisedHousingTotal,approvedHousing,approvedHousingTotal,spaceEquipmentPublicBuilding,spaceEquipmentPublicBuildingTotal,road,roadTotal,greenSpace,greenSpaceTotal,other,otherTotal},requesterProposal{active,fifteenPercentSupervisedOrApproved,acceptedByAuthority},alternativePlanningDetail,assignmentLocation,projectDescription,otherComment}, deliveredPermit{totalCharge,financialGuaranteeRequested,financialGuaranteeRequestedItem{amountRequested,depositDate,bank,guaranteeNumber,liftDate,entryDate,comment},chargeNature{receivableRequest{active,date,comment},startOfWorkRelatingToCharges{active,date,comment},introductionOfPermitApplicationForCharges{active,date,comment},implementationWithin2Years{active,date,comment},completeRealization{active,date,comment}},chargeCash{paymentOfChargeByTheApplicantOnTime,sumWithOtherCharges,linkedCase,paymentDate,bankAccount,comment,startWorkWith6YearsAfterPayment,moneyReallocation,timeLimitExtension,timeLimitExtensionMotif,timeLimitExtensionNotification,timeLimitSuspension,timeLimitSuspensionMotif,timeLimitSuspensionNotification,workFinancedByCharge,workFinancedByChargeDate,workFinancedByChargeComment,authorityRefund,authorityRefundDate,authorityRefundComment},freeCession{cessionDoneByRequester,date,comment}}}}}",
 "variables": {
 "arg": {
 "identifiersGroupInputs": [
 { "identifiersInputs": [ {
 "identifier": {
 "key": "'.$uuid.'",
 "type": "UUID"
 } } ] } ] } }}',
        ]);

        try {
            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);
            if($statusCode == 200 && $content) {
                $content = $response->toArray();
            };
        } catch (TransportExceptionInterface $e) {
            var_dump($e->getMessage());
        }

        return $content['data'] ?? null;
    }

    public function getAuthorizationString(array $nova_api_env)
    {
        return base64_encode($nova_api_env['username'] . ":" . $nova_api_env['password']);
    }

    public static function getReferencesJson(?array $id_list, string $type = "ID")
    {
        $identifiers["identifiers"] = array();

        $nb = is_countable($id_list) ? count($id_list) : 0;

        for ($i = 0; $i < $nb; $i++) {
            $identifiers["identifiers"][$i]['identifier']['key'] = $id_list[$i];
            $identifiers["identifiers"][$i]['identifier']['type'] = $type;
            // CONTEXT
            $identifiers["identifiers"][$i]['identifier']['context'] = 'CASE';
        };

        return json_encode($identifiers);
    }

}
