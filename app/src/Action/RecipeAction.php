<?php

namespace App\Action;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use FileSystemCache;
use Thapp\XmlBuilder\XMLBuilder;
use Thapp\XmlBuilder\Normalizer;

final class RecipeAction
{
    private $fileXML = __DIR__ . '/../../../data/receita_facil.xml';

    public function __invoke(Request $request, Response $response, $args)
    {
        $amount = isset($args['amount']) ? $args['amount'] : 5;
        $forceFileCached = isset($request->getQueryParams()['forceFileCached']) ? $request->getQueryParams()['forceFileCached'] : false;

        FileSystemCache::$cacheDir = __DIR__ . '/../../../cache/tmp';
        $key = FileSystemCache::generateCacheKey('cache', null);
        $data = FileSystemCache::retrieve($key);

        if($data === false || $forceFileCached == true)
        {
            $json = json_decode(json_encode(simplexml_load_file($this->getFileXML())), true);
            $json = $json['item'];

            $data = array();
            for($i = 0; $i < $amount; $i++)
            {
                $indice = rand(0, (count($json)-1));

                $data[] = $json[$indice];

                unset($json[$indice]);
                shuffle($json);
            }

            FileSystemCache::store($key, $data, 259200);
        }

        $xmlBuilder = new XmlBuilder('root');
        $xmlBuilder->load($data);
        $xml_output = $xmlBuilder->createXML(true);
        $response->write($xml_output);
        $response = $response->withHeader('content-type', 'text/xml');
        return $response;
    }

    /**
     * @return string
     */
    public function getFileXML()
    {
        return $this->fileXML;
    }
}