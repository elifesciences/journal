<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\Listing;
use eLife\Patterns\ViewModel\Paragraph;
use eLife\Patterns\ViewModel\Table;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PrivacyController extends Controller
{
    public function privacyAction(Request $request) : Response
    {
        $arguments = $this->defaultPageArguments($request);

        $arguments['title'] = 'Privacy notice';

        $arguments['contentHeader'] = new ContentHeader($arguments['title']);

        $arguments['body'] = [
            new Paragraph('This Privacy Notice relates to data held and processed by eLife Sciences Publications, Ltd. For all queries relating to personal data and privacy, please contact us at <a href="mailto:data@elifesciences.org">data@elifesciences.org</a>.'),
            ArticleSection::collapsible('users', 'General website users', 2, $this->render(
                Listing::ordered([
                    '<a href="#users-for">What is your personal data used for, and why?</a>',
                    '<a href="#users-hold">What types of personal data do we hold?</a>',
                    '<a href="#users-share">Who do we share the data with, and why?</a>',
                    '<a href="#users-confidential">How do we keep the data confidential?</a>',
                    '<a href="#users-long">How long do we retain the data for?</a>',
                    '<a href="#users-rights">Your rights</a>',
                ], 'bullet'),
                ArticleSection::basic('What is your personal data used for, and why?', 3, $this->render(
                    new Paragraph('eLife collects information of the sort that web browsers typically make available (such as IP address, browser type, language preference and referring site). In addition we use cookies, including analytics and login cookies, and local storage. We use this information for the legitimate interests of administering the sites, malicious activity tracking, and business analysis and development. We also capture anonymised information related to how you interact with our products and services for the legitimate interest of business analysis and development. This allows us to understand better how our visitors use the website, and to improve the design of the site and the user experience, and decide where to target business development activities.'),
                    new Paragraph('We operate a permission-based email communication system, which allows the collection of personal information to send emails to people who have consented to receive them. Each email contains the option to unsubscribe. We may also use this data for the legitimate interest of business analysis and planning.'),
                    new Paragraph('Cookies allow us to make the website work more smoothly for users. For users who are logged in, the cookies allow them to move between pages and remain logged in. We use analytics cookies to allow a better understanding, at an anonymous aggregated level, of how users use the site, and broad categories of users, including their location. Local storage is an area of your browser used by websites to store information on your computer rather than making additional web requests, and can speed up browsing.'),
                    new Paragraph('Visitors who do not wish to have cookies placed on their computers should set their browsers to refuse cookies before using eLife’s websites, with the drawback that certain features of our websites may not function properly without the aid of cookies. You can modify your browser settings to notify you each time a cookie is sent to it and you can decide whether or not to accept. Alternatively, you can set your browser to refuse all cookies. Cookies that have already been set can be deleted at any time using your browser settings. The independent <a href="https://www.aboutcookies.org/">AboutCookies.org</a> website contains comprehensive information on how to modify your browser settings. You can conceal your true IP address by using a virtual private network (VPN). You can opt out of interaction tracking by enabling Do Not Track (DNT) in your browser.')
                ), 'users-for'),
                ArticleSection::basic('What types of personal data do we hold?', 3, $this->render(
                    new Paragraph('Cookies do not necessarily identify you personally, but if you also log in to a service on our site (such as to leave an annotation, or to interact with the submission or payments systems) then they can be matched to an individual. The analytics cookies used for site usage monitoring only allow us to see anonymous and aggregated information. IP addresses might potentially identify individual users, but only when combined with other information, which we do not do. The interaction data is anonymous.'),
                    new Paragraph('A minimum set of information is required in order to provide a permission-based email communication system. This includes but isn’t limited to basic contact information including: name and email address. We may also store information which you have provided related to your preferences.'),
                    new Paragraph('A cookie is a small text file that a website stores on a visitor’s computer to allow us to recognise a particular user during their visit to the site and, in some cases, across multiple visits.'),
                    new Paragraph('When you login to your ORCID account through our site (for example to leave an annotation, or to use the submission site) that interaction is directly with ORCID. We only receive from ORCID a user name and email address.')
                ), 'users-hold'),
                ArticleSection::basic('Who do we share the data with, and why?', 3, $this->render(
                    new Paragraph('We share your data with organisations that help us deliver our services. It is never disclosed to other organisations for them to use for other purposes.'),
                    new Paragraph('We share the data with the following classes of data processor:'),
                    Listing::unordered([
                        'the organisations that provide or support our submission, publication fee billing, and customer relationship management systems;',
                        'online data storage organisations;',
                        'analytics companies, solely for them to be able to provide us with aggregated analysed data.',
                    ], 'bullet'),
                    new Paragraph('Some of these individuals and organisations are outside the European Economic Area (EEA).'),
                    new Paragraph('We may also make public aggregated anonymised information about the operation of our websites, for example the numbers of users.'),
                    new Paragraph('If eLife, or substantially all of its assets, were acquired, or in the unlikely event that eLife went out of business or entered bankruptcy, personal information might be one of the assets that was acquired by a third party. In that case, the personal information might be used by a third party in accordance with this notice.')
                ), 'users-share'),
                ArticleSection::basic('How do we keep the data confidential?', 3, $this->render(
                    new Paragraph('We safeguard your data using appropriate administrative, physical and technical security measures.'),
                    new Paragraph('When we share your data with others, we have contractual arrangements with them that oblige them to safeguard the data, to use it only for the purposes we have requested, and not to disclose it to any other parties without our consent.'),
                    new Paragraph('The form of these contractual arrangements varies according to the location of the other party, and we employ stronger safeguards for parties outside the European Economic Area (EEA).')
                ), 'users-confidential'),
                ArticleSection::basic('How long do we retain the data for?', 3, $this->render(
                    new Paragraph('We keep your personal data for no longer than reasonably necessary for the purpose for which it was collected.'),
                    new Paragraph('Cookies and local storage are stored on your computer, and so are only available to us when you visit our site.'),
                    new Paragraph('Aggregated analytics data and interaction data are retained for as long as it is relevant to business analysis and site development.'),
                    new Paragraph('If you add annotations to our site then they remain there indefinitely, but you can login and delete them at any time.'),
                    new Paragraph('For data that has been provided with consent (such as email addresses) we will hold this for three years in order to provide the communications you have requested. After this period we will contact you to confirm whether you wish to continue to receive communications, or wish to unsubscribe.')
                ), 'users-long'),
                ArticleSection::basic('Your rights', 3, $this->render(
                    new Paragraph('You may request a copy of any personal data eLife holds about you, and request to have any incorrect data corrected.'),
                    new Paragraph('You may object to us processing your data.'),
                    new Paragraph('You may ask us to erase your data if we are using it for promotional purposes, and in some other limited cases.'),
                    new Paragraph('You may also customise how eLife uses your data for promotional purposes by visiting the <a href="https://crm.elifesciences.org/crm/preferences">preference management centre</a>.'),
                    new Paragraph('You may ask us to erase your data if we are using it for promotional purposes; the data is no longer necessary for the purpose for which we originally collected or processed it; we are relying on legitimate interests as the basis for processing, and you object to the processing of your data, and there is no overriding legitimate interest to continue this processing; or we have processed the personal data unlawfully.'),
                    new Paragraph('You may also ask us to stop processing your data in certain specific circumstances: you contest the accuracy of the personal data and we are verifying the accuracy; the data has been unlawfully processed and you oppose erasure and request restriction instead; we no longer need the personal data, but you need us to keep it in order to establish, exercise or defend a legal claim; or you have objected to us processing your data and we are considering whether our legitimate grounds override yours.'),
                    new Paragraph('To exercise these rights please contact <a href="mailto:data@elifesciences.org">data@elifesciences.org</a>.'),
                    new Paragraph('Since cookies are placed on your computer, it is impracticable for us to delete them at your request. Your browser software will allow you to delete cookies. The analytics information we hold is anonymised and aggregated, so it is not possible for us to delete the data relating to one individual.'),
                    new Paragraph('You may complain about our retention and processing of your personal data to the Information Commissioner’s Office, the supervisory authority for data protection issues in England and Wales.')
                ), 'users-rights')
            ), true),
            ArticleSection::collapsible('authors', 'Authors', 2, $this->render(
                new Paragraph('This section of the notice deals specifically with personal information held by eLife about the authors of manuscripts submitted for review. You should also refer to the <a href="#users">general website user section</a> of the notice, that relates to all users of our website, and not just those submitting manuscripts.'),
                Listing::ordered([
                    '<a href="#authors-for">What is your personal data used for, and why?</a>',
                    '<a href="#authors-hold">What types of personal data do we hold?</a>',
                    '<a href="#authors-share">Who do we share the data with, and why?</a>',
                    '<a href="#authors-confidential">How do we keep the data confidential?</a>',
                    '<a href="#authors-long">How long do we retain the data for?</a>',
                    '<a href="#authors-rights">Your rights</a>',
                ], 'bullet'),
                ArticleSection::basic('What is your personal data used for, and why?', 3, $this->render(
                    new Paragraph('We collect your personal data to allow us to review and publish your manuscript, and (where applicable) charge you a publication fee. In addition we may use your contact details for promoting our activities to you, unless you opt out.'),
                    new Paragraph('Almost all the personal data we collect about you is to enable us to perform our contract with you to review your manuscript, and if accepted, to publish it and charge you a publication fee. Without this data we are not able to perform this contract with you.'),
                    new Paragraph('We also use this data for the legitimate interest of identifying potential reviewers for other manuscripts, and for research or statistical purposes.'),
                    new Paragraph('We use a small part of that data, plus some additional data, for the legitimate interest of promoting our activities to you and seeking your opinions.'),
                    new Paragraph('We also use some of your data for the legitimate interest of business analysis and planning.')
                ), 'authors-for'),
                ArticleSection::basic('What types of personal data do we hold?', 3, $this->render(
                    new Paragraph('There is a minimum set of information that is needed to process a manuscript (principally contact details and manuscript details). The review process generates additional information (such as reviewers’ comments and authors’ responses, and updated versions of the manuscript). If we bill you for a publication fee then this involves additional payment information.'),
                    new Paragraph('Author and manuscript personal information collected at submission stage includes: name, email address, affiliation, institutional role, ORCID iD, contributions and competing interests and funding information.'),
                    new Paragraph('Additional information generated during the review and publication process includes: revised versions of the manuscript; correspondence between authors, eLife (and their agents) and editors, including the editors’ decision letter and author’s response; fee waiver requests and whether a waiver was granted. If you pay the publication fee by credit card, your credit card details will be passed directly to our credit card processor; we do not see or retain those details.'),
                    new Paragraph('For promoting our services to you we use email address and affiliation; information about manuscripts submitted, and whether they were accepted; preferences you have expressed about the type of information you wish to receive.'),
                    new Paragraph('For business analysis and planning purposes we use: author and funder details; decisions taken on manuscript and related processing dates; names of editors and reviewers who handled the manuscript.'),
                    new Paragraph('Author and manuscript personal information collected at submission stage includes: name, email address, affiliation, institutional role, ORCID iD, contributions and competing interests and funding information.'),
                    new Paragraph('We also request, for research purposes, the group leader\'s country of residence, gender and the year in which they became an independent researcher.')
                ), 'authors-hold'),
                ArticleSection::basic('Who do we share the data with, and why?', 3, $this->render(
                    new Paragraph('We share your data with editors and reviewers, and with organisations that help us deliver our services.'),
                    new Paragraph('In addition, if your manuscript is published, then all the personal information normally associated with a published manuscript will be made public. In line with normal practice, we will also send this data, or make it available, to other publishing services, repositories and indexing services. Unless we have agreed with you otherwise, the public reviews will also be posted to bioRxiv or medRxiv.'),
                    new Paragraph('We share the data with the following classes of data processor:'),
                    Listing::unordered([
                        'our editors and reviewers;',
                        'the organisations that provide our submission, editorial support, production and publication fee billing, and accounting services and systems;',
                        'online data storage organisations.',
                    ], 'bullet'),
                    new Paragraph('We only share as much information as is necessary for each purpose.'),
                    new Paragraph('If you ask us to, we may share some of your data with organisations such as Dryad, Publons, ORCID and bioRxiv.'),
                    new Paragraph('If your manuscript is rejected, and if you ask us to, we may pass data to another journal to which you are submitting the manuscript.'),
                    new Paragraph('Some of these individuals and organisations are outside the European Economic Area (EEA).'),
                    new Paragraph('If eLife, or substantially all of its assets, were acquired, or in the unlikely event that eLife went out of business or entered bankruptcy, personal information might be one of the assets that was acquired by a third party. In that case, the personal information might be used by a third party in accordance with this notice.')
                ), 'authors-share'),
                ArticleSection::basic('How do we keep the data confidential?', 3, $this->render(
                    new Paragraph('We safeguard your data using appropriate administrative, physical and technical security measures.'),
                    new Paragraph('When we share your data with others, we have contractual arrangements with them that oblige them to safeguard the data, to use it only for the purposes we have requested, and not to disclose it to any other parties without our consent.'),
                    new Paragraph('The form of these contractual arrangements varies according to the location of the other party, and we employ stronger safeguards for parties outside the EEA.'),
                    new Paragraph('As our editors and reviewers carry out their roles as individuals, it is impracticable for us to have such comprehensive contractual arrangements with them as we have with other third parties. We therefore obtain your consent, at the time you submit your manuscript, to us sharing data with them.')
                ), 'authors-confidential'),
                ArticleSection::basic('How long do we retain the data for?', 3, $this->render(
                    new Paragraph('If your manuscript is published, then the associated personal information is retained indefinitely to allow us to deal with errors or other concerns that arise after publication.'),
                    new Paragraph('If your manuscript is rejected we retain the related personal information long enough to deal with appeals and re-submissions, and relevant business analysis.'),
                    new Paragraph('Rejected manuscript personal data is retained long enough to allow us to effectively handle appeals, papers that have been revised and newly submitted, for enquiries relating to research and publication ethics, and to allow us to undertake research or business analysis (for example, to help detect implicit bias and/or improve the review process for future submissions).'),
                    new Paragraph('Data related to publication fees will be retained for as long as required for legal and accounting requirements.')
                ), 'authors-long'),
                ArticleSection::basic('Your rights', 3, $this->render(
                    new Paragraph('You may request a copy of any personal data eLife holds about you, and request to have any incorrect data corrected.'),
                    new Paragraph('Where we are processing your data on the basis of a legitimate interest, which applies to business analysis and planning, and promotional activities, you may object to us processing your data.'),
                    new Paragraph('You may ask us to erase your data if we are using it for promotional purposes, and in some other limited cases.'),
                    new Paragraph('You may also customise how we use your data for promotional purposes by visiting your <a href="https://crm.elifesciences.org/crm/preferences">preferences centre</a>.'),
                    new Paragraph('You may ask us to erase your data if we are using it for promotional purposes; the data is no longer necessary for the purpose for which we originally collected or processed it; we are relying on legitimate interests as the basis for processing, and you object to the processing of your data, and there is no overriding legitimate interest to continue this processing; or we have processed the personal data unlawfully.'),
                    new Paragraph('You may also ask us to stop processing your data in certain specific circumstances: you contest the accuracy of the personal data and we are verifying the accuracy; the data has been unlawfully processed and you oppose erasure and request restriction instead; we no longer need the personal data, but you need us to keep it in order to establish, exercise or defend a legal claim; or you have objected to us processing your data and we are considering whether our legitimate grounds override yours.'),
                    new Paragraph('To exercise these rights please contact <a href="mailto:data@elifesciences.org">data@elifesciences.org</a>.'),
                    new Paragraph('You may complain about our retention and processing of your personal data to the Information Commissioner’s Office, the supervisory authority for data protection issues in England and Wales.')
                ), 'authors-rights')
            ), true),
            ArticleSection::collapsible('editors', 'Editors and reviewers', 2, $this->render(
                new Paragraph('This section of the notice deals specifically with personal information held by eLife about editors and reviewers  (editors include guest editors as well as members of the editorial board). You should also refer to the <a href="#users">general website user section</a> of the notice , that relates to all users of our website.'),
                Listing::ordered([
                    '<a href="#editors-for">What is your personal data used for, and why?</a>',
                    '<a href="#editors-hold">What types of personal data do we hold?</a>',
                    '<a href="#editors-share">Who do we share the data with, and why?</a>',
                    '<a href="#editors-confidential">How do we keep the data confidential?</a>',
                    '<a href="#editors-long">How long do we retain the data for?</a>',
                    '<a href="#editors-rights">Your rights</a>',
                ], 'bullet'),
                ArticleSection::basic('What is your personal data used for, and why?', 3, $this->render(
                    new Paragraph('We collect your personal data to allow us to administer the review of manuscripts that you perform, and in some cases to pay you a fee. In addition we may use your contact details for promoting our activities to you, unless you opt out.'),
                    new Paragraph('Almost all the personal data we collect about you is to enable us to perform our contract with you under which you provide reviewing services, and for which we may pay you a fee. Without this data we are not able to perform this contract with you.'),
                    new Paragraph('In some cases we also have a legal obligation to report fee payments to tax authorities.'),
                    new Paragraph('We use a small part of that data, plus some additional data, for the legitimate interest of promoting our activities to you and seeking your opinions.'),
                    new Paragraph('We also use some of your data for the legitimate interest of business analysis and planning.')
                ), 'editors-for'),
                ArticleSection::basic('What types of personal data do we hold?', 3, $this->render(
                    new Paragraph('To administer reviewing activities we hold your contact information and details about your specialisms and availability. The review process generates additional information, such as your review comments and other correspondence.'),
                    new Paragraph('The basic contact information we request includes: name, email address, phone number, affiliation, institutional role and ORCID iD. We will also have information about your specialisms, and this may be supplemented with personal information you have chosen to make public at ORCID, such as institution or employer, and past published papers. You may also have provided biographical information for publication on eLife’s website and in other eLife publications.'),
                    new Paragraph('Additional information generated during reviewing activities includes: information about manuscripts you have reviewed (and others that we have requested you to review), comments and decisions you have made and general correspondence, and dates of reviewing events, acceptance rates and processing times. We will also record your participation in eLife activities such as conference calls, meetings and exhibitions.'),
                    new Paragraph('For promoting our services to you we use email address and affiliation, specialisms and preferences you have expressed about the type of information you wish to receive.'),
                    new Paragraph('If we pay you a fee, then we will have details of your bank account and personal address, and in some cases, your personal tax reference number.')
                ), 'editors-hold'),
                ArticleSection::basic('Who do we share the data with, and why?', 3, $this->render(
                    new Paragraph('We share your data with other editors and reviewers, and with organisations that help us deliver our services. In some cases, where you have consented, your name will be shown on a published manuscript that you have reviewed, and so will be public.'),
                    new Paragraph('We share the data with the following classes of data processor:'),
                    Listing::unordered([
                        'other editors and reviewers;',
                        'the organisations that provide our submission, editorial support, production, accounting services and accounting systems;',
                        'online data storage organisations.',
                    ], 'bullet'),
                    new Paragraph('Data related to the payment of fees is in some cases shared with tax authorities.'),
                    new Paragraph('We only share as much information as is necessary for each purpose. Some of these individuals and organisations are outside the European Economic Area (EEA).'),
                    new Paragraph('If eLife, or substantially all of its assets, were acquired, or in the unlikely event that eLife went out of business or entered bankruptcy, personal information might be one of the assets that was acquired by a third party. In that case, the personal information might be used by a third party in accordance with this notice.')
                ), 'editors-share'),
                ArticleSection::basic('How do we keep the data confidential?', 3, $this->render(
                    new Paragraph('We safeguard your data using appropriate administrative, physical and technical security measures.'),
                    new Paragraph('When we share your data with others, we have contractual arrangements with them that oblige them to safeguard the data, to use it only for the purposes we have requested, and not to disclose it to any other parties without our consent.'),
                    new Paragraph('The form of these contractual arrangements varies according to the location of the other party, and we employ stronger safeguards for parties outside the EEA.')
                ), 'editors-confidential'),
                ArticleSection::basic('How long do we retain the data for?', 3, $this->render(
                    new Paragraph('Data related to a manuscript is retained as long as the rest of the manuscript data is retained. Data related to the payment of fees is retained for as long as required for legal and accounting requirements.'),
                    new Paragraph('The data related to a manuscript will be your basic contact data, review comments and discussions. We retain published manuscript information indefinitely to allow us to deal with errors or other concerns that arise after publication. We retain rejected manuscript information long enough to deal with appeals and re-submissions, and related business analysis.')
                ), 'editors-long'),
                ArticleSection::basic('Your rights', 3, $this->render(
                    new Paragraph('You may request a copy of any personal data eLife holds about you, and request to have any incorrect data corrected.'),
                    new Paragraph('Where we are processing your data on the basis of a legitimate interest, which applies to business analysis and planning, and promotional activities, you may object to us processing your data.'),
                    new Paragraph('You may ask us to erase your data if we are using it for promotional purposes, and in some other limited cases.'),
                    new Paragraph('You may also customise how we use your data for promotional purposes by visiting your <a href="https://crm.elifesciences.org/crm/preferences">preferences centre</a>.'),
                    new Paragraph('You may ask us to erase your data if we are using it for promotional purposes; the data is no longer necessary for the purpose for which we originally collected or processed it; we are relying on legitimate interests as the basis for processing, and you object to the processing of your data, and there is no overriding legitimate interest to continue this processing; or we have processed the personal data unlawfully.'),
                    new Paragraph('You may also ask us to stop processing your data in certain specific circumstances: you contest the accuracy of the personal data and we are verifying the accuracy; the data has been unlawfully processed and you oppose erasure and request restriction instead; we no longer need the personal data, but you need us to keep it in order to establish, exercise or defend a legal claim; or you have objected to us processing your data and we are considering whether our legitimate grounds override yours.'),
                    new Paragraph('To exercise these rights please contact <a href="mailto:data@elifesciences.org">data@elifesciences.org</a>.'),
                    new Paragraph('You may complain about our retention and processing of your personal data to the Information Commissioner’s Office, the supervisory authority for data protection issues in England and Wales.')
                ), 'editors-rights')
            ), true),
            ArticleSection::collapsible('changes', 'Privacy notice changes', 2, $this->render(
                new Paragraph('Although most changes are likely to be minor, eLife may change its Privacy Notice from time to time, and at our sole discretion. We encourage visitors to check this page frequently for any changes to its Privacy Notice. First published March 30, 2012.'),
                ArticleSection::basic('Change log', 3, $this->render(
                    new Table([<<<EOT
<table>
<tbody>
<tr>
<th>Dec&nbsp;5,&nbsp;2012</th>
<td>Added paragraphs on cookies.</td>
</tr>
<tr>
<th>Apr&nbsp;3,&nbsp;2012</th>
<td>Removed section on advertisers.</td>
</tr>
<tr>
<th>May&nbsp;23,&nbsp;2018</th>
<td>Completely restructured and expanded to comply with new legislation. Sections on author and editor data added.</td>
</tr>
<tr>
<th>Nov&nbsp;12,&nbsp;2018</th>
<td>Clarified use of author data for research purposes and identifying reviewers.</td>
</tr>
<tr>
<th>Mar&nbsp;12,&nbsp;2019</th>
<td>Added explanation of use of anonymised data about how users interact with our sites.</td>
</tr>
<tr>
<th>Jul&nbsp;24,&nbsp;2019</th>
<td>Added reference to information collected and used for research purposes about the group leader with principal responsibility for a submission.</td>
</tr>
</tbody>
</table>
EOT
                        ,
                    ])
                ))
            ), true),
        ];

        return new Response($this->get('templating')->render('::privacy.html.twig', $arguments));
    }
}
