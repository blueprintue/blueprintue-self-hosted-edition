<?php

declare(strict_types=1);

namespace app\controllers;

use Psr\Http\Message\ServerRequestInterface;
use Rancoud\Security\Security;
use Rancoud\Session\Session;

trait FormTrait
{
    /**
     * @throws \Exception
     */
    protected function setAndKeepInfos(string $key, string $value): void
    {
        Session::setFlash($key, $value);
        Session::keepFlash([$key]);
    }

    /**
     * @throws \Exception
     */
    protected function hasSentForm(ServerRequestInterface $request, string $method, array $inputs, string $errorKey): bool
    {
        if ($request->getMethod() !== $method) {
            return false;
        }

        $rawParams = $request->getParsedBody();

        $csrf = Session::get('csrf');
        if (empty($csrf) || !isset($rawParams[$inputs['CSRF']]) || $csrf !== $rawParams[$inputs['CSRF']]) {
            return false;
        }

        foreach ($inputs as $key => $input) {
            if ($key === 'CSRF') {
                continue;
            }

            if (!isset($rawParams[$input])) {
                $this->setAndKeepInfos($errorKey, 'Error, missing fields');

                return false;
            }

            // avoid bad encoding string
            try {
                Security::escHTML($rawParams[$input]);
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }
}
