<?php

namespace Matchish\ScoutElasticSearch\Engines;

use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;
use Matchish\ScoutElasticSearch\ElasticSearch\Index;
use Matchish\ScoutElasticSearch\ElasticSearch\Params\Bulk;
use Matchish\ScoutElasticSearch\Pipelines\ImportPipeline;

class ElasticSearchEngine extends Engine
{
    /**
     * The ElasticSearch client.
     *
     * @var \Elasticsearch\Client
     */
    protected $elasticsearch;

    /**
     * Create a new engine instance.
     *
     * @param  \Elasticsearch\Client $elasticsearch
     * @return void
     */
    public function __construct(\Elasticsearch\Client $elasticsearch)
    {
        $this->elasticsearch = $elasticsearch;
    }

    /**
     * @inheritdoc
     */
    public function update($models)
    {
        $params = new Bulk();
        $params->index($models);
        $this->elasticsearch->bulk($params->toArray());
    }

    /**
     * @inheritdoc
     */
    public function delete($models)
    {
        $payload = new Bulk();
        $payload->delete($models);
        $this->elasticsearch->bulk($payload->toArray());
    }


    /**
     * @inheritdoc
     */
    public function flush($model)
    {
        $indexName = $model->searchableAs();
        $params = [
            'index' => $indexName,
            'body' => ["query" => ['match_all' =>  new \stdClass()]
            ]
        ];
        $this->elasticsearch->deleteByQuery($params);
    }

    /**
     * @inheritdoc
     */
    public function search(Builder $builder)
    {
    }

    /**
     * @inheritdoc
     */
    public function paginate(Builder $builder, $perPage, $page)
    {

    }

    /**
     * @inheritdoc
     */
    public function mapIds($results)
    {
    }

    /**
     * @inheritdoc
     */
    public function map(Builder $builder, $results, $model)
    {

    }
    /**
     * @inheritdoc
     */
    public function getTotalCount($results)
    {
    }


    /**
     * @internal
     */
    public function sync($model)
    {
        $pipeline = new ImportPipeline($this->elasticsearch);
        $pipeline->process([Index::fromSearchable($model), $model]);
    }
}
