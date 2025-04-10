<?php

namespace PhpApi\Model\Request;

use PhpApi\Enum\InputParamType;
use Sapien\Request;

abstract class AbstractRequest
{
    public readonly Request $request;

    public function __construct(
        Request $request,
    ) {
        $this->request = $request;

        $paramTypes = RequestParser::getParamTypes(self::class, $this->request->method->name);

        foreach ($paramTypes as $paramType) {
            if ($paramType->type === InputParamType::Query) {
                $queryParamValue = $this->request->query[$paramType->name] ?? null;
                if (isset($queryParamValue)) {
                    $this->{$paramType->propertyName} = $queryParamValue;
                }
            } elseif ($paramType->type === InputParamType::Json) {
                $jsonParamValue = $this->request->json[$paramType->name] ?? null;
                if (isset($jsonParamValue)) {
                    $this->{$paramType->propertyName} = $jsonParamValue;
                }
            } elseif ($paramType->type === InputParamType::Input) {
                $inputParamValue = $this->request->input[$paramType->name] ?? null;
                if (isset($inputParamValue)) {
                    $this->{$paramType->propertyName} = $inputParamValue;
                }
            }
        }
    }
}
