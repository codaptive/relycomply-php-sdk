<?php

namespace Codaptive\RelyComply;

use GuzzleHttp\Exception\GuzzleException;

class Client
{
    private \GuzzleHttp\Client $httpClient;
    private string $endpoint = 'https://app.relycomply.com/graphql/';

    public function __construct(
        private readonly string $api_token
    )
    {
        $this->httpClient = new \GuzzleHttp\Client([
            'headers' => [
                'Accept-Encoding' => 'gzip',
                'User-Agent' => 'RelyComply PHP SDK',
                'Authorization' => 'Bearer ' . $this->api_token,
            ]
        ]);
    }

    /**
     * Execute a request against the GraphQL API.
     *
     * @param string $queryType
     * @param string $queryName
     * @param array|null $variables Variables for the GraphQL query
     *
     * @return array
     * @throws GuzzleException
     */
    private function request(string $queryType, string $queryName, array $variables = null): array
    {
        $filePath = sprintf(__DIR__ . '/graphql/%s/%s.graphql', $queryType, $queryName);

        $gqlRequest = [
            'query' => file_get_contents($filePath),
            'variables' => $variables
        ];

        $response = $this->httpClient->post($this->endpoint, [
            'json' => $gqlRequest,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get a list of customers.
     *
     * @throws GuzzleException
     */
    public function getCustomers(): array
    {
        return $this->request('query', 'customers');
    }

    /**
     * Get details of a customer.
     *
     * @throws GuzzleException
     */
    public function getCustomer(string $id): array
    {
        return $this->request('query', 'customers', [
            'id' => $id
        ]);
    }

    /**
     * Create a customer.
     *
     * @throws GuzzleException
     */
    public function createCustomer(array $customerData): array
    {
        return $this->request('mutation', 'createCustomer', [
            'input' => [
                'identifier' => $customerData['identifier'],
                'firstName' => $customerData['firstName'],
                'lastName' => $customerData['lastName'],
                'idDocumentType' => $customerData['idDocumentType'],
                'idDocumentCode' => $customerData['idDocumentCode'],
                'nationality' => $customerData['nationality'],
                'residence' => $customerData['residence'],
                'birthdate' => $customerData['birthdate'],
                'data' => [
                    [
                        'key' => 'address',
                        'value' => $customerData['address'],
                    ],
                    [
                        'key' => 'phone_number',
                        'value' => $customerData['phoneNumber'],
                    ]
                ],
            ],
        ]);
    }

    /**
     * Create an organization.
     *
     * @throws GuzzleException
     */
    public function createOrganization(array $customerData): array
    {
        return $this->request('mutation', 'createCustomer', [
            'input' => [
                'identifier' => $customerData['identifier'],
                'type' => 'organisation',
                'businessName' => $customerData['businessName'],
                'idDocumentType' => 'entity_registration',
                'idDocumentCode' => $customerData['idDocumentCode'],
                'nationality' => $customerData['nationality'],
                'residence' => $customerData['residence'],
                'birthdate' => $customerData['birthdate']
            ],
        ]);
    }

    /**
     * Get a list of assessments.
     *
     * @throws GuzzleException
     */
    public function getAssessments(): array
    {
        return $this->request('query', 'assessments');
    }

    /**
     * Get an assessment.
     *
     * @throws GuzzleException
     */
    public function getAssessment(string $id): array
    {
        return $this->request('query', 'assessments', [
            'id' => $id
        ]);
    }

    /**
     * Create an assessment.
     *
     * @throws GuzzleException
     */
    public function createAssessment(string $customerID, string $webhookUrl = null): array
    {
        return $this->request('mutation', 'createAssessment', [
            'input' => [
                'customer' => $customerID,
                'assessmentConfig' => 'onboarding_assessment',
                'webhookUrl' => $webhookUrl,
            ],
        ]);
    }

    /**
     * Create an assessment link.
     *
     * @throws GuzzleException
     */
    public function createAssessmentLink(string $primaryAssessment, string $relatedAssessment, string $role): array
    {

        return $this->request('mutation', 'createAssessmentLink', [
            'input' => [
                'primaryAssessment' => $primaryAssessment,
                'relatedAssessment' => $relatedAssessment,
                'role' => $role,
            ],
        ]);
    }

    /**
     * Create a document.
     *
     * @throws GuzzleException
     */
    public function createDocument(string $customerID, string $documentType, string $documentData): array
    {
        return $this->request('mutation', 'createDocument', [
            'input' => [
                'customer' => $customerID,
                'documentType' => $documentType,
                'data' => $documentData,
            ]
        ]);
    }
}
