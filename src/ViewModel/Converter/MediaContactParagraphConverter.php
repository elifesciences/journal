<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\MediaContact;
use eLife\ApiSdk\Model\Place;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Paragraph;

final class MediaContactParagraphConverter implements ViewModelConverter
{
    use CreatesDate;

    /**
     * @param MediaContact $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $details = array_merge(
            [$object->getDetails()->getPreferredName()],
            array_map(function (Place $affiliation) {
                return $affiliation->toString();
            }, $object->getAffiliations()),
            array_map(function (string $emailAddress) {
                return "<a href=\"mailto:$emailAddress\">$emailAddress</a>";
            }, $object->getEmailAddresses()),
            array_map(function (string $phoneNumber) {
                return "<a href=\"tel:$phoneNumber\">$phoneNumber</a>";
            }, $object->getPhoneNumbers())
        );

        return new Paragraph(implode('<br>', $details));
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof MediaContact;
    }
}
