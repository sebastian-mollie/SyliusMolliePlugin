<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Uploader;

use BitBag\SyliusMolliePlugin\Entity\MollieGatewayConfigInterface;
use Doctrine\Common\Collections\Collection;
use Gaufrette\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Webmozart\Assert\Assert;

final class PaymentMethodLogoUploader implements PaymentMethodLogoUploaderInterface
{
    /** @var Filesystem */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function upload(Collection $mollieGatewayConfigs): void
    {
        /** @var MollieGatewayConfigInterface $mollieGatewayConfig */
        foreach ($mollieGatewayConfigs as $mollieGatewayConfig) {
            Assert::notNull($mollieGatewayConfig->getCustomizeMethodImage());
            if ($mollieGatewayConfig->getCustomizeMethodImage()->hasFile()) {
                $this->uploadSingle($mollieGatewayConfig);
            }
        }
    }

    public function remove(string $path): bool
    {
        if ($this->filesystem->has($path)) {
            return $this->filesystem->delete($path);
        }

        return false;
    }

    private function uploadSingle(MollieGatewayConfigInterface $mollieGatewayConfig): void
    {
        $customizeImage = $mollieGatewayConfig->getCustomizeMethodImage();

        Assert::notNull($customizeImage);

        /** @var UploadedFile $file */
        $file = $customizeImage->getFile();

        if (null !== $customizeImage->getPath() && $this->fileExists($customizeImage->getPath())) {
            $this->remove($customizeImage->getPath());
        }

        do {
            $hash = md5(uniqid((string) mt_rand(), true));
            $path = $this->expandPath($hash . '.' . $file->guessExtension());
        } while ($this->isAdBlockingProne($path) || $this->filesystem->has($path));

        $customizeImage->setPath($path);
        $customizeImage->setName($file->getClientOriginalName());

        Assert::notNull($customizeImage->getPath());
        $fileContent = file_get_contents($file->getPathname());

        Assert::string($fileContent);

        $this->filesystem->write(
            $customizeImage->getPath(),
            $fileContent
        );
    }

    private function fileExists(string $path): bool
    {
        return $this->filesystem->has($path);
    }

    private function isAdBlockingProne(string $path): bool
    {
        return false !== strpos($path, 'ad');
    }

    private function expandPath(string $path): string
    {
        return sprintf(
            '%s/%s/%s',
            substr($path, 0, 2),
            substr($path, 2, 2),
            substr($path, 4)
        );
    }
}
