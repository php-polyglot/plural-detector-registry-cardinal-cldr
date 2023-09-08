<?php

declare(strict_types=1);

namespace Polyglot\CldrCardinalPluralDetectorRegistry;

use Polyglot\CallablePluralDetector\CallablePluralDetector;
use Polyglot\Contract\PluralDetector\PluralDetector;
use Polyglot\Contract\PluralDetector\PluralCategory as Plural;
use Polyglot\Contract\PluralDetectorRegistry\Exception\LocaleNotSupported;
use Polyglot\Contract\PluralDetectorRegistry\PluralDetectorRegistry;
use Polyglot\Number\Number;

/**
 * @link https://www.unicode.org/cldr/charts/43/supplemental/language_plural_rules.html
 */
final class CldrCardinalPluralDetectorRegistry implements PluralDetectorRegistry
{
    /**
     * @var array<string, PluralDetector>
     */
    private array $pluralDetectors = [];

    /**
     * @inheritDoc
     */
    public function get(string $locale): PluralDetector
    {
        if (!array_key_exists($locale, $this->pluralDetectors)) {
            $this->pluralDetectors[$locale] = $this->createPluralDetector($locale);
        }
        return $this->pluralDetectors[$locale];
    }

    /**
     * @param string $locale
     * @return CallablePluralDetector
     * @throws LocaleNotSupported
     */
    private function createPluralDetector(string $locale): CallablePluralDetector
    {
        switch ('pt_PT' !== $locale && strlen($locale) > 3 ? substr($locale, 0, strrpos($locale, '_')) : $locale) {
            case 'af':  // Afrikaans
            case 'an':  // Aragonese
            case 'asa': // Asu
            case 'az':  // Azerbaijani
            case 'bal': // Baluchi
            case 'bem': // Bemba
            case 'bez': // Bena
            case 'bg':  // Bulgarian
            case 'brx': // Bodo
            case 'ce':  // Chechen
            case 'cgg': // Chiga
            case 'chr': // Cherokee
            case 'ckb': // Central Kurdish
            case 'dv':  // Divehi
            case 'ee':  // Ewe
            case 'el':  // Greek
            case 'eo':  // Esperanto
            case 'eu':  // Basque
            case 'fo':  // Faroese
            case 'fur': // Friulian
            case 'gsw': // Swiss German
            case 'ha':  // Hausa
            case 'haw': // Hawaiian
            case 'hu':  // Hungarian
            case 'jgo': // Ngomba
            case 'jmc': // Machame
            case 'ka':  // Georgian
            case 'kaj': // Jju
            case 'kcg': // Tyap
            case 'kk':  // Kazakh
            case 'kkj': // Kako
            case 'kl':  // Kalaallisut
            case 'ks':  // Kashmiri
            case 'ksb': // Shambala
            case 'ku':  // Kurdish
            case 'ky':  // Kyrgyz
            case 'lb':  // Luxembourgish
            case 'lg':  // Ganda
            case 'mas': // Masai
            case 'mgo': // Meta'
            case 'ml':  // Malayalam
            case 'mn':  // Mongolian
            case 'mr':  // Marathi
            case 'nb':  // Norwegian Bokmål
            case 'ne':  // Nepali
            case 'nd':  // North Ndebele
            case 'nn':  // Norwegian Nynorsk
            case 'nnh': // Ngiemboon
            case 'no':  // Norwegian
            case 'nr':  // South Ndebele
            case 'ny':  // Nyanja
            case 'nyn': // Nyankole
            case 'om':  // Oromo
            case 'or':  // Odia
            case 'os':  // Ossetic
            case 'pap': // Papiamento
            case 'ps':  // Pashto
            case 'rm':  // Romansh
            case 'rwk': // Rwa
            case 'rof': // Rombo
            case 'saq': // Samburu
            case 'seh': // Sena
            case 'sd':  // Sindhi
            case 'sdh': // Southern Kurdish
            case 'sn':  // Shona
            case 'so':  // Somali
            case 'sq':  // Albanian
            case 'ss':  // Swati
            case 'st':  // Southern Sotho
            case 'ssy': // Saho
            case 'syr': // Syriac
            case 'ta':  // Tamil
            case 'te':  // Telugu
            case 'teo': // Teso
            case 'tig': // Tigre
            case 'tk':  // Turkmen
            case 'tn':  // Tswana
            case 'tr':  // Turkish
            case 'ts':  // Tsonga
            case 'ug':  // Uyghur
            case 'uz':  // Uzbek
            case 've':  // Venda
            case 'vo':  // Volapük
            case 'vun': // Vunjo
            case 'wae': // Walser
            case 'xh':  // Xhosa
            case 'xog': // Soga
                $fn = static function (Number $number): string {
                    $n = $number->number();
                    return $n == 1 ? Plural::ONE : Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::OTHER];
                break;

            case 'ak':  // Akan
            case 'bho': // Bhojpuri
            case 'ln':  // Lingala
            case 'mg':  // Malagasy
            case 'nso': // Northern Sotho
            case 'pa':  // Punjabi
            case 'ti':  // Tigrinya
            case 'wa':  // Walloon
                $fn = static function (Number $number): string {
                    $n = $number->number();
                    return ($n == 0 || $n == 1) ? Plural::ONE : Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::OTHER];
                break;

            case 'am':  // Amharic
            case 'as':  // Assamese
            case 'bn':  // Bangla
            case 'doi': // Dogri
            case 'fa':  // Persian
            case 'gu':  // Gujarati
            case 'hi':  // Hindi
            case 'kn':  // Kannada
            case 'pcm': // Nigerian Pidgin
            case 'zu':  // Zulu
                $fn = static function (Number $number): string {
                    $n = $number->number();
                    $i = $number->integer();
                    return ($i === 0 || $n == 1) ? Plural::ONE : Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::OTHER];
                break;

            case 'ar':  // Arabic
                $fn = static function (Number $number): string {
                    $n = $number->number();
                    if ($n == 0) {
                        return Plural::ZERO;
                    }
                    if ($n == 1) {
                        return Plural::ONE;
                    }
                    if ($n == 2) {
                        return Plural::TWO;
                    }
                    $modN100 = $n % 100;
                    if ($modN100 >= 3 && $modN100 <= 10) {
                        return Plural::FEW;
                    }
                    if ($modN100 >= 11) {
                        return Plural::MANY;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ZERO, Plural::ONE, Plural::TWO, Plural::FEW, Plural::MANY, Plural::OTHER];
                break;


            case 'ast': // Asturian
            case 'de':  // German
            case 'en':  // English
            case 'et':  // Estonian
            case 'fi':  // Finnish
            case 'fy':  // Western Frisian
            case 'gl':  // Galician
            case 'ia':  // Interlingua
            case 'io':  // Ido
            case 'ji':  // Yiddish
            case 'lij': // Ligurian
            case 'nl':  // Dutch
            case 'sc':  // Sardinian
            case 'scn': // Sicilian
            case 'sv':  // Swedish
            case 'sw':  // Swahili
            case 'ur':  // Urdu
            case 'yi':  // Yiddish
                $fn = static function (Number $number): string {
                    $i = $number->integer();
                    $v = $number->fractionDigits();
                    return ($i === 1 && $v === 0) ? Plural::ONE : Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::OTHER];
                break;

            case 'be':  // Belarusian
                $fn = static function (Number $number): string {
                    $n = $number->number();
                    $f = $number->fraction();
                    if ($f > 0) {
                        return Plural::OTHER;
                    }
                    $modN10 = $n % 10;
                    $modN100 = $n % 100;
                    if ($modN10 == 1 && $modN100 != 11) {
                        return Plural::ONE;
                    }
                    if ($modN10 >= 2 && $modN10 <= 4 && ($modN100 < 12 || $modN100 > 14)) {
                        return Plural::FEW;
                    }
                    if ($modN10 === 0 || $modN10 >= 5 || ($modN100 >= 11 && $modN100 <= 14)) {
                        return Plural::MANY;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::FEW, Plural::MANY, Plural::OTHER];
                break;

            case 'bm':  // Bambara
            case 'bo':  // Tibetan
            case 'dz':  // Dzongkha
            case 'hnj': // Hmong Njua
            case 'ig':  // Igbo
            case 'ii':  // Sichuan Yi
            case 'id':  // Indonesian
            case 'in':  // Indonesian
            case 'ja':  // Japanese
            case 'jbo': // Lojban
            case 'jv':  // Javanese
            case 'jw':  // Javanese
            case 'kea': // Kabuverdianu
            case 'kde': // Makonde
            case 'km':  // Khmer
            case 'ko':  // Korean
            case 'lkt': // Lakota
            case 'lo':  // Lao
            case 'ms':  // Malay
            case 'my':  // Burmese
            case 'nqo': // N'Ko
            case 'osa': // Osage
            case 'sah': // Yakut
            case 'ses': // Koyraboro Senni
            case 'sg':  // Sango
            case 'su':  // Sundanese
            case 'th':  // Thai
            case 'to':  // Tongan
            case 'tpi': // Tok Pisin
            case 'yue': // Cantonese
            case 'zh':  // Chinese
            case 'vi':  // Vietnamese
            case 'wo':  // Wolof
            case 'yo':  // Yoruba
                $fn = function (): string {
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::OTHER];
                break;

            case 'br':  // Breton
                $fn = static function (Number $number): string {
                    $n = $number->number();
                    $f = $number->fraction();
                    $modN10 = $n % 10;
                    $modN100 = $n % 100;
                    if ($f > 0) {
                        return Plural::OTHER;
                    }
                    if ($modN10 === 1 && !in_array($modN100, [11, 71, 91], true)) {
                        return Plural::ONE;
                    }
                    if ($modN10 === 2 && !in_array($modN100, [12, 72, 92], true)) {
                        return Plural::TWO;
                    }
                    if (
                        in_array($modN10, [3, 4, 9], true)
                        && ($modN100 < 10 || $modN100 > 19)
                        && ($modN100 < 70 || $modN100 > 79)
                        && ($modN100 < 90)
                    ) {
                        return Plural::FEW;
                    }
                    if ($n != 0 && $n % 1000000 === 0) {
                        return Plural::MANY;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::TWO, Plural::FEW, Plural::MANY, Plural::OTHER];
                break;

            case 'bs':  // Bosnian
            case 'hr':  // Croatian
            case 'sr':  // Serbian
            case 'sh':  // Serbo-Croatian
                $fn = static function (Number $number): string {
                    $v = $number->fractionDigits();
                    $i = $number->integer();
                    $f = $number->fraction();
                    $modI10 = $i % 10;
                    $modI100 = $i % 100;
                    $modF10 = $f % 10;
                    $modF100 = $f % 100;
                    if (
                        ($v === 0 && $modI10 === 1 && $modI100 !== 11)
                        || ($modF10 === 1 && $modF100 !== 11)
                    ) {
                        return Plural::ONE;
                    }
                    if (
                        ($v === 0 && in_array($modI10, [2, 3, 4], true) && !in_array($modI100, [12, 13, 14], true))
                        || (in_array($modF10, [2, 3, 4], true) && !in_array($modF100, [12, 13, 14], true))
                    ) {
                        return Plural::FEW;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::FEW, Plural::OTHER];
                break;

            case 'ca':  // Catalan
            case 'vec':  // Venetian
                $fn = static function (Number $number): string {
                    $i = $number->integer();
                    $v = $number->fractionDigits();
                    if ($i === 1 && $v === 0) {
                        return Plural::ONE;
                    }
                    $e = $number->exponent();
                    $modI1000000 = $i % 1000000;
                    if (
                        ($e === 0 && $i !== 0 && $modI1000000 === 0 && $v === 0)
                        || $e > 5
                    ) {
                        return Plural::MANY;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::MANY, Plural::OTHER];
                break;

            case 'ceb': // Cebuano
                $fn = static function (Number $number): string {
                    $i = $number->integer();
                    $v = $number->fractionDigits();
                    $f = $number->fraction();
                    $modI10 = $i % 10;
                    $modF10 = $f % 10;
                    if (
                        ($v === 0 && $i >= 1 && $i <= 3)
                        || ($v === 0 && !in_array($modI10, [4, 6, 9], true))
                        || ($v !== 0 && !in_array($modF10, [4, 6, 9], true))
                    ) {
                        return Plural::ONE;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::OTHER];
                break;

            case 'cs': // Czech
            case 'sk': // Slovak
                $fn = static function (Number $number): string {
                    $v = $number->fractionDigits();
                    if ($v !== 0) {
                        return Plural::MANY;
                    }
                    $i = $number->integer();
                    if ($i === 1) {
                        return Plural::ONE;
                    }
                    if ($i >= 2 && $i <= 4) {
                        return Plural::FEW;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::FEW, Plural::MANY, Plural::OTHER];
                break;

            case 'cy':  // Welsh
                $fn = static function (Number $number): string {
                    $n = $number->number();
                    if ($n == 0) {
                        return Plural::ZERO;
                    }
                    if ($n == 1) {
                        return Plural::ONE;
                    }
                    if ($n == 2) {
                        return Plural::TWO;
                    }
                    if ($n == 3) {
                        return Plural::FEW;
                    }
                    if ($n == 6) {
                        return Plural::MANY;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ZERO, Plural::ONE, Plural::TWO, Plural::FEW, Plural::MANY, Plural::OTHER];
                break;

            case 'da': // Danish
                $fn = static function (Number $number): string {
                    $n = $number->number();
                    $t = $number->fraction(false);
                    $i = $number->integer();

                    if ($n == 1 || ($t !== 0 && in_array($i, [0, 1], true))) {
                        return Plural::ONE;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::OTHER];
                break;

            case 'dsb': // Lower Sorbian
            case 'hsb': // Upper Sorbian
                $fn = static function (Number $number): string {
                    $v = $number->fractionDigits();
                    $modI100 = $number->integer() % 100;
                    $modF100 = $number->fraction() % 100;
                    if (($v === 0 && $modI100 === 1) || $modF100 === 1) {
                        return Plural::ONE;
                    }
                    if (($v === 0 && $modI100 === 2) || $modF100 === 2) {
                        return Plural::TWO;
                    }
                    if (($v === 0 && in_array($modI100, [3, 4], true)) || in_array($modF100, [3, 4], true)) {
                        return Plural::FEW;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::TWO, Plural::FEW, Plural::OTHER];
                break;

            case 'es':     // Spanish
                $fn = static function (Number $number): string {
                    $n = $number->number();
                    $i = $number->integer();
                    $v = $number->fractionDigits();
                    if ($n == 1) {
                        return Plural::ONE;
                    }
                    $e = $number->exponent();
                    $modI1000000 = $i % 1000000;
                    if (
                        ($e === 0 && $i !== 0 && $modI1000000 === 0 && $v === 0)
                        || !in_array($e, [0, 1, 2, 3, 4, 5], true)
                    ) {
                        return Plural::MANY;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::MANY, Plural::OTHER];
                break;

            case 'ff':  // Fula
            case 'hy':  // Armenian
            case 'kab': // Kabyle
                $fn = static function (Number $number): string {
                    $i = $number->integer();
                    if ($i === 0 || $i === 1) {
                        return Plural::ONE;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::OTHER];
                break;

            case 'fil': // Filipino
            case 'tl':  // Tagalog
                $fn = static function (Number $number): string {
                    $i = $number->integer();
                    $v = $number->fractionDigits();
                    $modI10 = $i % 10;
                    $modF10 = $number->fraction() % 10;
                    if (
                        ($v === 0 && in_array($i, [1, 2, 3], true))
                        || ($v === 0 && !in_array($modI10, [4, 6, 9], true))
                        || ($v !== 0 && !in_array($modF10, [4, 6, 9], true))
                    ) {
                        return Plural::ONE;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::OTHER];
                break;

            case 'fr':  // French
                $fn = static function (Number $number): string {
                    $i = $number->integer();
                    if ($i === 0 || $i === 1) {
                        return Plural::ONE;
                    }
                    $e = $number->exponent();
                    $modI1000000 = $i % 1000000;
                    $v = $number->fractionDigits();
                    if (
                        ($e === 0 && $modI1000000 === 0 && $v === 0)
                        || !in_array($e, [0, 1, 2, 3, 4, 5], true)
                    ) {
                        return Plural::MANY;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::MANY, Plural::OTHER];
                break;

            case 'ga': // Irish
                $fn = static function (Number $number): string {
                    $n = $number->number();
                    if ($n == 1) {
                        return Plural::ONE;
                    }
                    if ($n == 2) {
                        return Plural::TWO;
                    }
                    $f = $number->fraction();
                    $i = $number->integer();
                    if ($f === 0 && in_array($i, [3, 4, 5, 6], true)) {
                        return Plural::FEW;
                    }
                    if ($f === 0 && in_array($i, [7, 8, 9, 10], true)) {
                        return Plural::MANY;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::TWO, Plural::FEW, Plural::MANY, Plural::OTHER];
                break;

            case 'gd':  // Scottish Gaelic
                $fn = static function (Number $number): string {
                    $n = $number->number();
                    if ($n == 1 || $n == 11) {
                        return Plural::ONE;
                    }
                    if ($n == 2 || $n == 12) {
                        return Plural::TWO;
                    }
                    $f = $number->fraction();
                    $i = $number->integer();
                    if ($f === 0 && (($i >= 3 && $i <= 10) || ($i >= 13 && $i <= 19))) {
                        return Plural::FEW;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::TWO, Plural::FEW, Plural::OTHER];
                break;

            case 'gv': // Manx
                $fn = static function (Number $number): string {
                    $v = $number->fractionDigits();
                    $i = $number->integer();
                    $modI10 = $i % 10;
                    if ($v === 0 && $modI10 === 1) {
                        return Plural::ONE;
                    }
                    if ($v === 0 && $modI10 === 2) {
                        return Plural::TWO;
                    }
                    $modI100 = $i % 100;
                    if ($v === 0 && in_array($modI100, [0, 20, 40, 60, 80], true)) {
                        return Plural::FEW;
                    }
                    if ($v !== 0) {
                        return Plural::MANY;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::TWO, Plural::FEW, Plural::MANY, Plural::OTHER];
                break;

            case 'is':  // Icelandic
                $fn = static function (Number $number): string {
                    $i = $number->integer();
                    $t = $number->fraction(false);
                    $modI10 = $i % 10;
                    $modI100 = $i % 100;
                    $modT10 = $t % 10;
                    $modT100 = $t % 100;
                    if (
                        ($t === 0 && $modI10 === 1 && $modI100 !== 11)
                        || ($modT10 === 1 && $modT100 !== 11)
                    ) {
                        return Plural::ONE;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::OTHER];
                break;

            case 'it':     // Italian
            case 'pt_PT':  // European Portuguese
                $fn = static function (Number $number): string {
                    $i = $number->integer();
                    $v = $number->fractionDigits();
                    if ($i === 1 && $v === 0) {
                        return Plural::ONE;
                    }
                    $e = $number->exponent();
                    $modI1000000 = $i % 1000000;
                    if (
                        ($e === 0 && $i !== 0 && $modI1000000 === 0 && $v === 0)
                        || !in_array($e, [0, 1, 2, 3, 4, 5], true)
                    ) {
                        return Plural::MANY;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::MANY, Plural::OTHER];
                break;

            case 'iu':  // Inuktitut
            case 'naq': // Nama
            case 'sat': // Santali
            case 'se':  // Northern Sami
            case 'sma': // Southern Sami
            case 'smj': // Lule Sami
            case 'smn': // Inari Sami
            case 'sms': // Skolt Sami
                $fn = static function (Number $number): string {
                    $n = $number->number();
                    if ($n == 1) {
                        return Plural::ONE;
                    }
                    if ($n == 2) {
                        return Plural::TWO;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::TWO, Plural::OTHER];
                break;

            case 'ksh': // Colognian
                $fn = static function (Number $number): string {
                    $n = $number->number();
                    if ($n == 0) {
                        return Plural::ZERO;
                    }
                    if ($n == 1) {
                        return Plural::ONE;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ZERO, Plural::ONE, Plural::OTHER];
                break;

            case 'kw': // Cornish
                $fn = static function (Number $number): string {
                    $n = $number->number();
                    if ($n == 0) {
                        return Plural::ZERO;
                    }
                    if ($n == 1) {
                        return Plural::ONE;
                    }
                    $modN100 = $n % 100;
                    $modN1000 = $n % 1000;
                    $modN100000 = $n % 100000;
                    $modN1000000 = $n % 1000000;
                    if (in_array($modN100, [2, 22, 42, 62, 82], true) || $modN1000000 === 100000) {
                        return Plural::TWO;
                    }
                    if (
                        $modN1000 === 0
                        && (
                            ($modN100000 >= 1000 && $modN100000 <= 20000)
                            || in_array($modN100000, [40000, 60000, 80000], true)
                        )
                    ) {
                        return Plural::TWO;
                    }
                    if (in_array($modN100, [3, 23, 43, 63, 83], true)) {
                        return Plural::FEW;
                    }
                    if (in_array($modN100, [1, 21, 41, 61, 81], true)) {
                        return Plural::MANY;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ZERO, Plural::ONE, Plural::TWO, Plural::FEW, Plural::MANY, Plural::OTHER];
                break;

            case 'lag': // Langi
                $fn = static function (Number $number): string {
                    $n = $number->number();
                    if ($n == 0) {
                        return Plural::ZERO;
                    }
                    $i = $number->integer();
                    if ($i === 0 || $i === 1) {
                        return Plural::ONE;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ZERO, Plural::ONE, Plural::OTHER];
                break;

            case 'lt': // Lithuanian
                $fn = static function (Number $number): string {
                    $f = $number->fraction();
                    if ($f !== 0) {
                        return Plural::MANY;
                    }
                    $n = $number->number();
                    $modN10 = $n % 10;
                    $modN100 = $n % 100;
                    if ($modN10 === 1 && ($modN100 < 11 || $modN100 > 19)) {
                        return Plural::ONE;
                    }
                    if ($modN10 >= 1 && ($modN100 < 11 || $modN100 > 19)) {
                        return Plural::FEW;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::FEW, Plural::MANY, Plural::OTHER];
                break;

            case 'lv': //  Latvian
            case 'prg': // Prussian
                $fn = static function (Number $number): string {
                    $n = $number->number();
                    $v = $number->fractionDigits();
                    $f = $number->fraction();
                    $modN10 = $n % 10;
                    $modN100 = $n % 100;
                    $modF100 = $f % 100;
                    $modF10 = $f % 10;

                    if (
                        ($modN10 === 1 && $modN100 != 11)
                        || ($v === 2 && $modF10 === 1 && $modF100 != 11)
                        || ($v !== 2 && $modF10 === 1)
                    ) {
                        return Plural::ONE;
                    }
                    if (
                        ($modN10 === 0)
                        || ($modN100 >= 11 && $modN100 <= 19)
                        || ($v === 2 && $modF100 >= 11 && $modF100 <= 19)
                    ) {
                        return Plural::ZERO;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ZERO, Plural::ONE, Plural::OTHER];
                break;

            case 'mk': // Macedonian
                $fn = static function (Number $number): string {
                    $v = $number->fractionDigits();
                    $i = $number->integer();
                    $f = $number->fraction();
                    $modI10 = $i % 10;
                    $modI100 = $i % 100;
                    $modF10 = $f % 10;
                    $modF100 = $f % 100;
                    if (
                        ($v === 0 && $modI10 === 1 && $modI100 !== 11)
                        || ($modF10 === 1 && $modF100 !== 11)
                    ) {
                        return Plural::ONE;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::OTHER];
                break;

            case 'mo': // Moldavian
            case 'ro': // Romanian
                $fn = static function (Number $number): string {
                    $v = $number->fractionDigits();
                    $i = $number->integer();
                    if ($v === 0 && $i === 1) {
                        return Plural::ONE;
                    }
                    $n = $number->number();
                    $modN100 = $n % 100;
                    if (
                        ($v !== 0)
                        || ($n == 0)
                        || ($n != 1 && $modN100 >= 1 && $modN100 <= 19)
                    ) {
                        return Plural::FEW;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::FEW, Plural::OTHER];
                break;

            case 'mt': // Maltese
                $fn = static function (Number $number): string {
                    $n = $number->number();
                    if ($n == 1) {
                        return Plural::ONE;
                    }
                    if ($n == 2) {
                        return Plural::TWO;
                    }
                    $modN100 = $n % 100;
                    if ($n == 0 || ($modN100 >= 3 && $modN100 <= 10)) {
                        return Plural::FEW;
                    }
                    if ($modN100 >= 11 && $modN100 <= 19) {
                        return Plural::MANY;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::TWO, Plural::FEW, Plural::MANY, Plural::OTHER];
                break;

            case 'pl': // Polish
                $fn = static function (Number $number): string {
                    $v = $number->fractionDigits();
                    $i = $number->integer();
                    if ($v === 0 && $i === 1) {
                        return Plural::ONE;
                    }
                    $modI10 = $i % 10;
                    $modI100 = $i % 100;
                    if ($v === 0 && in_array($modI10, [2, 3, 4], true) && !in_array($modI100, [12, 13, 14], true)) {
                        return Plural::FEW;
                    }
                    if (
                        ($v === 0 && $i !== 1 && $modI10 >= 0 && $modI10 <= 1)
                        || ($v === 0 && $modI10 >= 5)
                        || ($v === 0 && $modI100 >= 12 && $modI100 <= 14)
                    ) {
                        return Plural::MANY;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::FEW, Plural::MANY, Plural::OTHER];
                break;

            case 'pt':  // Portuguese
                $fn = static function (Number $number): string {
                    $i = $number->integer();
                    $v = $number->fractionDigits();
                    if ($i === 0 || $i === 1) {
                        return Plural::ONE;
                    }
                    $e = $number->exponent();
                    $modI1000000 = $i % 1000000;
                    if (
                        ($e === 0 && $modI1000000 === 0 && $v === 0)
                        || !in_array($e, [0, 1, 2, 3, 4, 5], true)
                    ) {
                        return Plural::MANY;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::MANY, Plural::OTHER];
                break;

            case 'ru':  // Russian
            case 'uk':  // Ukrainian
                $fn = static function (Number $number): string {
                    $v = $number->fractionDigits();
                    if ($v !== 0) {
                        return Plural::OTHER;
                    }
                    $modI10 = $number->integer() % 10;
                    $modI100 = $number->integer() % 100;
                    if ($modI10 === 1 && $modI100 !== 11) {
                        return Plural::ONE;
                    }
                    if ($modI10 >= 2 && $modI10 <= 4 && ($modI100 < 12 || $modI100 > 14)) {
                        return Plural::FEW;
                    }
                    if ($modI10 === 0 || $modI10 >= 5 || ($modI100 >= 11 && $modI100 <= 14)) {
                        return Plural::MANY;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::FEW, Plural::MANY, Plural::OTHER];
                break;

            case 'shi':  // Tachelhit
                $fn = static function (Number $number): string {
                    $i = $number->integer();
                    $n = $number->number();
                    $f = $number->fraction();
                    if ($i === 0 || $n == 1) {
                        return Plural::ONE;
                    }
                    if ($f === 0 && $i >= 2 && $n <= 10) {
                        return Plural::FEW;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::FEW, Plural::OTHER];
                break;

            case 'si':  // Sinhala
                $fn = static function (Number $number): string {
                    $n = $number->number();
                    $i = $number->integer();
                    $f = $number->fraction();
                    if (
                        ($n == 0 || $n == 1)
                        || ($i === 0 && $f === 1)
                    ) {
                        return Plural::ONE;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::OTHER];
                break;

            case 'sl':  // Slovenian
                $fn = static function (Number $number): string {
                    $v = $number->fractionDigits();
                    $modI100 = $number->integer() % 100;
                    if ($v === 0 && $modI100 === 1) {
                        return Plural::ONE;
                    }
                    if ($v === 0 && $modI100 === 2) {
                        return Plural::TWO;
                    }
                    if ($v !== 0) {
                        return Plural::FEW;
                    }
                    if ($modI100 === 3 || $modI100 === 4) {
                        return Plural::FEW;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::TWO, Plural::FEW, Plural::OTHER];
                break;

            case 'tzm': // Central Atlas Tamazight
                $fn = static function (Number $number): string {
                    $f = $number->fraction();
                    if ($f !== 0) {
                        return Plural::OTHER;
                    }
                    $i = $number->integer();
                    if ($i === 0 || $i === 1 || ($i >= 11 && $i <= 99)) {
                        return Plural::ONE;
                    }
                    return Plural::OTHER;
                };
                $allowedCategories = [Plural::ONE, Plural::OTHER];
                break;

            default:
                throw new LocaleNotSupported(sprintf('locale %s not supported', $locale));
        }
        return new CallablePluralDetector($fn, $allowedCategories);
    }
}
