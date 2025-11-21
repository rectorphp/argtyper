<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\FormHelper;

use Argtyper202511\RectorPrefix202511\Nette\Utils\Strings;
use Argtyper202511\Rector\Symfony\Contract\Tag\TagInterface;
use Argtyper202511\Rector\Symfony\DataProvider\ServiceMapProvider;
final class FormTypeStringToTypeProvider
{
    /**
     * @readonly
     * @var \Rector\Symfony\DataProvider\ServiceMapProvider
     */
    private $serviceMapProvider;
    /**
     * @var array<string, string>
     */
    private const SYMFONY_CORE_NAME_TO_TYPE_MAP = ['form' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\FormType', 'birthday' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\BirthdayType', 'checkbox' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\CheckboxType', 'collection' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\CollectionType', 'country' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\CountryType', 'currency' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\CurrencyType', 'date' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\DateType', 'datetime' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\DatetimeType', 'email' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\EmailType', 'file' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\FileType', 'hidden' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\HiddenType', 'integer' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\IntegerType', 'language' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\LanguageType', 'locale' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\LocaleType', 'money' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\MoneyType', 'number' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\NumberType', 'password' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\PasswordType', 'percent' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\PercentType', 'radio' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\RadioType', 'range' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\RangeType', 'repeated' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\RepeatedType', 'search' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\SearchType', 'textarea' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\TextareaType', 'text' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\TextType', 'time' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\TimeType', 'timezone' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\TimezoneType', 'url' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\UrlType', 'button' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\ButtonType', 'submit' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\SubmitType', 'reset' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\ResetType', 'entity' => 'Argtyper202511\Symfony\Bridge\Doctrine\Form\Type\EntityType', 'choice' => 'Argtyper202511\Symfony\Component\Form\Extension\Core\Type\ChoiceType'];
    /**
     * @var array<string, string>
     */
    private $customServiceFormTypeByAlias = [];
    public function __construct(ServiceMapProvider $serviceMapProvider)
    {
        $this->serviceMapProvider = $serviceMapProvider;
    }
    public function matchClassForNameWithPrefix(string $name): ?string
    {
        $nameToTypeMap = $this->getNameToTypeMap();
        if (strncmp($name, 'form.type.', strlen('form.type.')) === 0) {
            $name = Strings::substring($name, strlen('form.type.'));
        }
        return $nameToTypeMap[$name] ?? null;
    }
    /**
     * @return array<string, string>
     */
    private function getNameToTypeMap(): array
    {
        $customServiceFormTypeByAlias = $this->provideCustomServiceFormTypeByAliasFromContainerXml();
        return array_merge(self::SYMFONY_CORE_NAME_TO_TYPE_MAP, $customServiceFormTypeByAlias);
    }
    /**
     * @return array<string, string>
     */
    private function provideCustomServiceFormTypeByAliasFromContainerXml(): array
    {
        if ($this->customServiceFormTypeByAlias !== []) {
            return $this->customServiceFormTypeByAlias;
        }
        $serviceMap = $this->serviceMapProvider->provide();
        $formTypeServiceDefinitions = $serviceMap->getServicesByTag('form.type');
        foreach ($formTypeServiceDefinitions as $formTypeServiceDefinition) {
            $formTypeTag = $formTypeServiceDefinition->getTag('form.type');
            if (!$formTypeTag instanceof TagInterface) {
                continue;
            }
            $alias = $formTypeTag->getData()['alias'] ?? null;
            if (!is_string($alias)) {
                continue;
            }
            $class = $formTypeServiceDefinition->getClass();
            if ($class === null) {
                continue;
            }
            $this->customServiceFormTypeByAlias[$alias] = $class;
        }
        return $this->customServiceFormTypeByAlias;
    }
}
