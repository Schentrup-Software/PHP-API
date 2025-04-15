<?php

namespace PhpApi\Model\Request;

use PhpApi\Enum\InputParamType;
use Sapien\Request;

abstract class AbstractRequest
{
    public readonly Request $request;

    // TODO: switch out this as a constructor to instead have this logic in the request parser.
    // The router will go into a private property that will get set in the router and will be
    // accessible via a get method.
    public function __construct(
        Request $request,
    ) {
        $this->request = $request;

        $paramTypes = RequestParser::getParamTypes($this::class, $this->request->method->name);

        foreach ($paramTypes as $paramType) {
            if ($paramType->type === InputParamType::Query) {
                $queryParamValue = $this->request->query[$paramType->name] ?? null;
                if (isset($queryParamValue) || !isset($this->{$paramType->propertyName})) {
                    $this->{$paramType->propertyName} = $queryParamValue;
                }
            } elseif ($paramType->type === InputParamType::Json) {
                $jsonParamValue = $this->request->json[$paramType->name] ?? null;
                if (isset($jsonParamValue) || !isset($this->{$paramType->propertyName})) {
                    $this->{$paramType->propertyName} = $jsonParamValue;
                }
            } elseif ($paramType->type === InputParamType::Input) {
                $inputParamValue = $this->request->input[$paramType->name] ?? null;
                if (isset($inputParamValue) || !isset($this->{$paramType->propertyName})) {
                    $this->{$paramType->propertyName} = $inputParamValue;
                }
            }
        }
    }
}
