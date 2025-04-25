<?php

declare(strict_types=1);

namespace app\controllers\www;

use app\helpers\Helper;
use app\services\www\BlueprintService;
use app\services\www\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rancoud\Application\Application;
use Rancoud\Application\ApplicationException;
use Rancoud\Http\Message\Factory\Factory;
use Rancoud\Http\Message\Stream;
use Rancoud\Http\Message\UploadedFile;
use Rancoud\Session\Session;

class UploadController implements MiddlewareInterface
{
    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     * @throws ApplicationException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $entity = $request->getAttribute('entity');
        $subEntity = $request->getAttribute('subEntity');

        if ($entity === 'user' && $subEntity === 'avatar') {
            return $this->uploadUserAvatar($request);
        }

        if ($entity === 'blueprint' && $subEntity === 'thumbnail') {
            return $this->uploadBlueprintThumbnail($request);
        }

        return (new Factory())->createResponse(404)->withBody(Stream::create());
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Model\ModelException
     * @throws ApplicationException
     */
    protected function uploadUserAvatar(ServerRequestInterface $request): ResponseInterface
    {
        $nameInFiles = 'avatar';

        $userID = Session::get('userID');
        if ($userID === null) {
            $this->cleanFiles($nameInFiles);

            return $this->sendError('user not logged');
        }

        $entityID = (int) $request->getAttribute('entityID');
        if ($userID !== $entityID) {
            $this->cleanFiles($nameInFiles);

            return $this->sendError('user forbidden');
        }

        try {
            $folder = Application::getFolder('MEDIAS_AVATARS');
        } catch (ApplicationException $e) {
            $this->cleanFiles($nameInFiles);

            return $this->sendError('folder for avatars not found');
        }

        [$filename, $error] = $this->treatUpload($request, $nameInFiles, $folder);
        $this->cleanFiles($nameInFiles);
        if ($error !== null) {
            return $this->sendError($error);
        }

        $filepath = $folder . $filename;
        if (UserService::updateAvatar($userID, $filename) === false) {
            if (\file_exists($filepath) && \is_file($filepath)) {
                \unlink($filepath);
            }

            return $this->sendError('could not update user avatar');
        }

        return $this->sendSuccess('/medias/avatars/' . $filename);
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     * @throws ApplicationException
     */
    protected function uploadBlueprintThumbnail(ServerRequestInterface $request): ResponseInterface
    {
        $nameInFiles = 'thumbnail';

        $userID = Session::get('userID');
        if ($userID === null) {
            $this->cleanFiles($nameInFiles);

            return $this->sendError('user not logged');
        }

        $entityID = (int) $request->getAttribute('entityID');

        if (!BlueprintService::isAuthorBlueprint($entityID, $userID)) {
            $this->cleanFiles($nameInFiles);

            return $this->sendError('user not author');
        }

        try {
            $folder = Application::getFolder('MEDIAS_BLUEPRINTS');
        } catch (ApplicationException $e) {
            $this->cleanFiles($nameInFiles);

            return $this->sendError('folder for thumbnails not found');
        }

        [$filename, $error] = $this->treatUpload($request, $nameInFiles, $folder);
        $this->cleanFiles($nameInFiles);
        if ($error !== null) {
            return $this->sendError($error);
        }

        if (BlueprintService::updateThumbnail($entityID, $filename) === false) {
            // @codeCoverageIgnoreStart
            // it's hard to produce error on unlink without alter folder permissions
            $filepath = $folder . $filename;
            if (\file_exists($filepath) && \is_file($filepath)) {
                \unlink($filepath);
            }

            return $this->sendError('could not update blueprint thumbnail');
            // @codeCoverageIgnoreEnd
        }

        return $this->sendSuccess('/medias/blueprints/' . $filename);
    }

    /** @throws \Exception */
    protected function treatUpload(ServerRequestInterface $request, string $name, string $folder): array
    {
        $uploadParameters = $this->extractUploadParameters($request);
        if ($uploadParameters === null) {
            return [null, 'invalid parameters'];
        }

        if (
            $uploadParameters['canvas_width'] !== 310 || $uploadParameters['canvas_height'] !== 310
            || $uploadParameters['mask_width'] !== 200 || $uploadParameters['mask_height'] !== 200
            || $uploadParameters['mask_x'] !== 55 || $uploadParameters['mask_y'] !== 55
        ) {
            return [null, 'invalid constraints parameters'];
        }

        $files = $request->getUploadedFiles();
        if (!isset($files[$name])) {
            return [null, 'missing file'];
        }

        // @var UploadedFile $avatarRawFile
        $avatarRawFile = $files[$name];

        if ($this->isValidFile($avatarRawFile, $uploadParameters) === false) {
            return [null, 'invalid file'];
        }

        $imgDest = null;

        try {
            $imgDest = $this->createImageInMemory($avatarRawFile, $uploadParameters);
            if ($imgDest === null) {
                // @codeCoverageIgnoreStart
                // I don't know how to produce this error
                throw new \Exception();
                // @codeCoverageIgnoreEnd
            }

            $filename = $this->writeImageInStorage($imgDest, $folder);

            \imagedestroy($imgDest);
        } catch (\Exception $e) {
            if ($imgDest !== null) {
                // @codeCoverageIgnoreStart
                // I don't know how to produce this error
                \imagedestroy($imgDest);
                // @codeCoverageIgnoreEnd
            }

            return [null, 'could not use image'];
        }

        if ($filename === null) {
            // @codeCoverageIgnoreStart
            // I don't know how to produce this error
            return [null, 'could not save image'];
            // @codeCoverageIgnoreEnd
        }

        return [$filename, null];
    }

    /** @throws \Exception */
    protected function extractUploadParameters(ServerRequestInterface $request): ?array
    {
        $params = [];
        $rawParams = $request->getParsedBody();
        $props = ['canvas_width', 'canvas_height', 'mask_width', 'mask_height', 'mask_x', 'mask_y'];
        foreach ($props as $prop) {
            if (!isset($rawParams[$prop])) {
                return null;
            }

            $params[$prop] = (int) $rawParams[$prop];
            if ($params[$prop] <= 0) {
                return null;
            }
        }

        $csrf = Session::get('csrf');
        if (empty($csrf) || !isset($rawParams['csrf']) || ($rawParams['csrf'] !== Session::get('csrf'))) {
            return null;
        }

        return $params;
    }

    protected function cleanFiles(string $idxName): void
    {
        if (isset($_FILES[$idxName]) && \is_string($_FILES[$idxName]['tmp_name'])) {
            $filepath = $_FILES[$idxName]['tmp_name'];
            if (\file_exists($filepath) && \is_file($filepath)) {
                \unlink($filepath);
            }
        }
    }

    protected function isValidFile(UploadedFile $file, array $uploadParameters): bool
    {
        if ($file->getClientMediaType() !== 'image/png') {
            return false;
        }

        if ($file->getSize() < 1) {
            return false;
        }

        if ($file->getError() !== 0) {
            return false;
        }

        if (!\file_exists($file->getFilename())) {
            return false;
        }

        $finfo = new \finfo(\FILEINFO_MIME_TYPE);
        if ($file->getClientMediaType() !== $finfo->file($file->getFilename())) {
            return false;
        }

        $imageSize = \getimagesize($file->getFilename());

        return !($imageSize[0] !== $uploadParameters['canvas_width'] || $imageSize[1] !== $uploadParameters['canvas_height']);
    }

    protected function createImageInMemory(UploadedFile $file, array $uploadParameters): ?\GdImage
    {
        $imgSrc = @\imagecreatefrompng($file->getFilename());
        if ($imgSrc === false) {
            // @codeCoverageIgnoreStart
            // With Dockerfile gd returns false, but with Github Action it retuns a resource
            return null;
            // @codeCoverageIgnoreEnd
        }

        $imgDest = \imagecreatetruecolor($uploadParameters['mask_width'], $uploadParameters['mask_height']);
        if ($imgDest === false) {
            // @codeCoverageIgnoreStart
            // I don't know how to produce this error
            return null;
            // @codeCoverageIgnoreEnd
        }

        if (\imagealphablending($imgDest, false) === false) {
            // @codeCoverageIgnoreStart
            // I don't know how to produce this error
            \imagedestroy($imgSrc);

            return null;
            // @codeCoverageIgnoreEnd
        }

        if (\imagecopy($imgDest, $imgSrc, 0, 0, $uploadParameters['mask_x'], $uploadParameters['mask_y'], $uploadParameters['mask_width'], $uploadParameters['mask_height']) === false) {
            // @codeCoverageIgnoreStart
            // I don't know how to produce this error
            \imagedestroy($imgSrc);

            return null;
            // @codeCoverageIgnoreEnd
        }

        if (\imagesavealpha($imgDest, true) === false) {
            // @codeCoverageIgnoreStart
            // I don't know how to produce this error
            \imagedestroy($imgSrc);

            return null;
            // @codeCoverageIgnoreEnd
        }

        \imagedestroy($imgSrc);

        return $imgDest;
    }

    /** @throws \Exception */
    protected function writeImageInStorage($imgDest, string $folder): ?string
    {
        do {
            $file = \mb_strtolower(Helper::getRandomString(60) . '.png');
        } while (\file_exists($folder . \DIRECTORY_SEPARATOR . $file));

        if (\imagepng($imgDest, $folder . \DIRECTORY_SEPARATOR . $file, 9) === false) {
            // @codeCoverageIgnoreStart
            // I don't know how to produce this error
            return null;
            // @codeCoverageIgnoreEnd
        }

        return $file;
    }

    /** @throws \Exception */
    protected function sendError(string $error): ResponseInterface
    {
        $body = \json_encode(['message' => $error], \JSON_THROW_ON_ERROR);

        return (new Factory())->createResponse(400)->withBody(Stream::create($body))->withHeader('Content-type', 'application/json');
    }

    /** @throws \Exception */
    protected function sendSuccess(string $fileUrl): ResponseInterface
    {
        $body = \json_encode(['file_url' => $fileUrl], \JSON_THROW_ON_ERROR);

        return (new Factory())->createResponse()->withBody(Stream::create($body))->withHeader('Content-type', 'application/json');
    }
}
