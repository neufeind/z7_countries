<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Service;

use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Zeroseven\Countries\Model\Country;

class LanguageManipulationService
{
    public const BASE_DELIMITER = '-';

    protected static function getOriginalLanguage(SiteLanguage $language): SiteLanguage
    {
        $languageId = $language->getLanguageId();
        $originalLanguages = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('country', 'originalLanguages');

        return $originalLanguages[$languageId] ?? $language;
    }

    protected static function cleanString(string $string, int $maxLength = null, bool $forceLowercase = null): string
    {
        $string = preg_replace('/[^a-z0-9_-]/i', '', $string);

        if ($forceLowercase) {
            $string = strtolower($string);
        }

        if ($maxLength) {
            $string = substr($string, 0, $maxLength);
        }

        return $string;
    }

    protected static function cleanIsoCode(string $string, int $maxLength = null, bool $forceLowercase = null)
    {
        return self::cleanString(preg_replace('/[^a-z]/i', '', $string), $maxLength, $forceLowercase);
    }

    public static function getBase(SiteLanguage $language, Country $country = null): UriInterface
    {
        if ($country && ($parameter = $country->getParameter())) {
            return $language->getBase()->withPath('/' . self::cleanIsoCode($language->getTwoLetterIsoCode(), 2, true) . self::BASE_DELIMITER . self::cleanString($parameter) . '/');
        }

        return self::getOriginalLanguage($language)->getBase();
    }

    public static function getHreflang(SiteLanguage $language, Country $country = null): string
    {
        if ($country && ($isoCode = $country->getIsoCode())) {
            return self::cleanIsoCode($language->getTwoLetterIsoCode(), 2, true) . '-' . self::cleanIsoCode($isoCode, 2);
        }

        return self::getOriginalLanguage($language)->getHreflang();
    }
}
