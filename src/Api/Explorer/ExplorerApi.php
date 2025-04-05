<?php

namespace Mollsoft\LaravelEthereumModule\Api\Explorer;

use Illuminate\Support\Facades\Http;
use Mollsoft\LaravelEthereumModule\Api\DTOPaginator;
use Mollsoft\LaravelEthereumModule\Api\Explorer\DTO\ApiLimitDTO;
use Mollsoft\LaravelEthereumModule\Api\Explorer\DTO\TokenTransactionDTO;
use Mollsoft\LaravelEthereumModule\Api\Explorer\DTO\TransactionDTO;

class ExplorerApi
{
    protected string $baseURL, $apiKey;

    public function __construct(string $baseURL, string $apiKey)
    {
        $this->baseURL = $baseURL;
        $this->apiKey = $apiKey;
    }

    public function request(array $params): mixed
    {
        $response = Http::get($this->baseURL, [
            ...$params,
            'apikey' => $this->apiKey,
        ]);

        $result = $response->json();

        if (isset($result['error'])) {
            throw new \Exception($result['error']['message'] ?? $result['error']);
        }

        return $result['status'] === '1' ? $result['result'] : [];
    }

    /**
     * @return array<TransactionDTO>
     */
    public function getTransactions(string $address, int $startBlock = 0, int $limit = 10, int $page = 1): array
    {
        $data = $this->request([
            'module' => 'account',
            'action' => 'txlist',
            'address' => $address,
            'startblock' => $startBlock,
            'endblock' => '99999999',
            'sort' => 'desc',
            'page' => $page,
            'offset' => $limit,
        ]);

        return array_map(fn($item) => TransactionDTO::make($item), $data);
    }

    /**
     * @return DTOPaginator<TransactionDTO>
     */
    public function getTransactionsPaginator(string $address, int $startBlock = 0, int $perPage = 10): DTOPaginator
    {
        return new DTOPaginator(
            callback: function (int $page) use ($address, $startBlock, $perPage) {
                return $this->getTransactions($address, $startBlock, $perPage, $page);
            },
            perPage: $perPage
        );
    }

    /**
     * @return array<TokenTransactionDTO>
     */
    public function getTransactionsOfToken(
        string $address,
        ?string $contract = null,
        int $startBlock = 0,
        int $limit = 10,
        int $page = 1,
    ): array {
        $params = [
            'module' => 'account',
            'action' => 'tokentx',
            'address' => $address,
            'startblock' => $startBlock,
            'endblock' => '99999999',
            'sort' => 'desc',
            'page' => $page,
            'offset' => $limit,
        ];

        if ($contract) {
            $params['contractaddress'] = $contract;
        }

        $data = $this->request($params);

        return array_map(fn($item) => TokenTransactionDTO::make($item), $data);
    }

    /**
     * @return DTOPaginator<TokenTransactionDTO>
     */
    public function getTokenTransactionsPaginator(
        string $address,
        ?string $contract = null,
        int $startBlock = 0,
        int $perPage = 10,
    ): DTOPaginator {
        return new DTOPaginator(
            callback: function (int $page) use ($address, $contract, $startBlock, $perPage) {
                return $this->getTransactionsOfToken($address, $contract, $startBlock, $perPage, $page);
            },
            perPage: $perPage
        );
    }

    public function getApiLimit(): ApiLimitDTO
    {
        $data = $this->request([
            'module' => 'getapilimit',
            'action' => 'getapilimit',
        ]);

        return ApiLimitDTO::make($data);
    }
}
