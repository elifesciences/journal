<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\Listing;
use eLife\Patterns\ViewModel\Paragraph;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TermsController extends Controller
{
    public function termsAction(Request $request) : Response
    {
        $arguments = $this->defaultPageArguments($request);

        $arguments['title'] = 'Terms and policy';

        $arguments['contentHeader'] = new ContentHeader($arguments['title']);

        $comments = $this->isGranted('FEATURE_CAN_USE_HYPOTHESIS') ? 'Annotations' : 'Comments';

        $arguments['body'] = [
            ArticleSection::basic('Terms and Conditions of Use', 2, $this->render(
                ArticleSection::basic('Welcome and Consent to Terms', 3, $this->render(
                    new Paragraph('eLife Sciences Publications, Ltd. ("eLife") is a 501(c)(3) nonprofit
                          initiative for the very best in science and science communication. In
                          furtherance of our mission, eLife operates websites, including the websites at
                          elifesciences.org, submit.elifesciences.org, payments.elifesciences.org and
                          other eLife websites (the "eLife Sites"). We offer use of the eLife journal
                          content free of charge to the public through the eLife Sites. Access to and
                          use of the eLife Sites is provided by eLife subject to the following Terms and
                          Conditions, which are a contract between eLife and you. Use of the eLife Sites
                          constitutes your acceptance of these Terms and Conditions. If you do not
                          accept these Terms and Conditions in full, you do not have permission to
                          access and use the eLife Sites and should cease doing so immediately.')
                )),
                ArticleSection::basic('Ownership', 3, $this->render(
                    new Paragraph('The eLife Sites, the software ("eLife Software"), application programming
                          interfaces ("eLife APIs"), content and trademarks used on or in connection
                          with the eLife Sites are owned by eLife or its licensors, and are subject to
                          US and international intellectual property rights and laws. Nothing contained
                          herein shall be construed as conferring by implication, or otherwise any
                          license or right under any trademark, copyright or patent of eLife or any
                          other third party and all rights are reserved, except as explicitly stated in
                          these Terms and Conditions.')
                )),
                ArticleSection::basic('License to Use Journal Articles and Related Content', 3, $this->render(
                    new Paragraph('Unless otherwise indicated, the articles and journal content published by
                          eLife on the eLife Sites are licensed under a <a
                            href="https://creativecommons.org/licenses/by/4.0/">Creative Commons
                            Attribution license</a> (also known as a CC-BY license). This means that you
                          are free to use, reproduce and distribute the articles and related content
                          (unless otherwise noted), for commercial and noncommercial purposes, subject
                          to citation of the original source in accordance with the CC-BY license.')
                )),
                ArticleSection::basic('Impermissible Uses', 3, $this->render(
                    new Paragraph('You agree to use the eLife Sites only for lawful purposes, and in a manner
                              that does not infringe the rights of, or restrict or inhibit the use of the
                              eLife Sites by any third party. Such restriction or inhibition includes,
                              without limitation, conduct which is unlawful, or which may harass or cause
                              distress or inconvenience to any person and the transmission of obscene or
                              offensive content or disruption of normal flow of dialogue within this
                              website. As a condition to your use of the eLife Sites, you agree not to:'),
                    Listing::unordered([
                        'upload, post, e-mail, transmit, or otherwise make available any
                                    information, materials, or other content that is illegal, harmful,
                                    threatening, abusive, harassing, defamatory, obscene, pornographic, or
                                    offensive; or that infringes another&apos;s rights, including any intellectual
                                    property rights;',
                        'impersonate any person or entity or falsely state or otherwise
                                    misrepresent your affiliation with a person or entity; or obtain, collect,
                                    store, or modify personal information about other users;',
                        'upload, post, e-mail, transmit, or otherwise make available to any user of
                                      the eLife Sites any unsolicited or unauthorized advertising, promotional
                                      materials, &quot;junk mail&quot;, &quot;spam&quot;, &quot;chain letters&quot; &quot;pyramid schemes&quot; or any
                                      other form of solicitation;',
                        'modify, adapt, or hack the eLife Sites or falsely imply that some other site is associated with the eLife Sites or eLife; or',
                        'use the eLife Sites for any illegal or unauthorized purpose. You must not,
                                  in the use of the eLife Sites, violate any US laws or laws in your
                                  jurisdiction (including but not limited to copyright laws).',
                    ], 'bullet')
                )),
                ArticleSection::basic('Suspension of Use / Violation of these Terms and Conditions', 3, $this->render(
                    new Paragraph('eLife reserves the right to limit, suspend, or terminate your access and use
                              of the eLife Sites at any time without notice. eLife reserves the right to
                              investigate and prosecute violations of any of these Terms and Conditions to
                              the fullest extent of the law. eLife may involve and cooperate with law
                              enforcement authorities in prosecuting users who violate these Terms and
                              Conditions. You acknowledge that eLife has no obligation to pre-screen or
                              monitor your access to or use of the eLife Sites, but has the right to do so.
                              You hereby agree that eLife may, in the exercise of eLife&apos;s sole discretion,
                              remove or delete any entries, information, materials or other content that
                              violates these Terms and Conditions or that is otherwise objectionable.')
                )),
                ArticleSection::basic('Disclaimer of Warranty', 3, $this->render(
                    new Paragraph('The eLife Sites, including, without limitation, the eLife Software, the eLife
                              APIs and the content on the eLife Sites (e.g., the articles, information,
                              data, names, images, pictures, logos and icons), whether belonging to eLife or
                              a third party, are provided &quot;AS IS&quot; and on an &quot;AS AVAILABLE&quot; basis without any
                              representation or endorsement made and without warranty of any kind whether
                              express or implied, including but not limited to ANY implied warranties of
                              satisfactory quality, merchantability, fitness for a particular purpose,
                              non-infringement, compatibility, security, completeness and accuracy. Nothing
                              contained on the eLife Sites is intended to be medical or health advice and
                              eLife strongly recommends that you independently verify any medical or health
                              advice or treatment information on which you chose to rely. eLife, ITS
                              DIRECTORS, OFFICERS, EMPLOYEES, AGENTS and FUNDERS (COLLECTIVELY, the "ELIFE
                              PARTIES"), its software vendors, developers or other consultants working on
                              the eLife Sites do not warrant that the eLife Sites, the eLife Software, the
                              eLife APIs, and the content on the eLife Sites will be uninterrupted or error
                              free, that defects will be corrected, or that the eLife Sites or the server
                              that makes THEM available are free of viruses or bugs or represents the full
                              functionality, accuracy, AND reliability of the materials.')
                )),
                ArticleSection::basic('Limitation of Liability', 3, $this->render(
                    new Paragraph('To the fullest extent permitted by law, in no event will the eLife Parties,
                              the software vendors, developers or other consultants working on the eLife
                              Sites be liable for any losses, damages, liabilities, costs or claims
                              including, without limitation, direct, indirect, punative or consequential
                              damages, and loss of use, data or profits, arising out of or in connection
                              with the use of the eLife Sites, the eLife Software, the elife APIs, or any
                              content on the eLife Sites or linked sources, whether in action of contract,
                              negligence or otherwise. In no event shall the liability of the eLife Parties,
                              the software vendors, developers, or other consultants exceed the amount paid
                              by you to eLife hereunder in the proceeding twelve months (other than any
                              indemnity payment). IN ALL CASES, THE ELIFE PARTIES, THE SOFTWARE VENDORS,
                              DEVELOPERS OR OTHER CONSULTANTS SHALL NOT BE LIABLE FOR ANY LOSS OR DAMAGE
                              THAT WAS NOT REASONABLY FORESEEABLE.')
                )),
                ArticleSection::basic('Indemnity', 3, $this->render(
                    new Paragraph('You are solely responsible for your actions in connection with the eLife
                              Sites and any internet costs incurred in accessing the sites. You agree to
                              indemnify and hold harmless, and at eLife&apos;s option defend, the eLife Parties,
                              the software vendors, developers, consultants or other persons working on the
                              eLife Sites from and against any liability, damage, loss, cost or expenses
                              (including reasonable attorneys&apos; fees) in connection with your use of the
                              eLife Sites, including, without limitation, the eLife Software, the eLife
                              APIs, and content on the eLife Sites, and for the results of any action you
                              may take based on the material contained herein, and from any consequences of
                              the following:'),
                    Listing::unordered([
                        'your failure to comply with any of these Terms and Conditions;',
                        'unlawful disclosure or alteration of or interference of whatsoever nature by
                                  any person with the content of any transmission by you or on your behalf by
                                  means of a public telecommunication system or of information concerning such
                                  transmission or the use made of such system by any person; and',
                        'any act or omission by you constituting breach of copyright, infringement of
                                  trademark rights, or breach of any other intellectual property rights of
                                  whatsoever nature, or rights similar thereto anywhere in the world,
                                  belonging to any other person, or any allegation of any of the foregoing.',
                    ], 'bullet')
                )),
                ArticleSection::basic('Interactive Features', 3, $this->render(
                    new Paragraph('In the course of using the eLife Sites, you may provide eLife with feedback,
                              including but not limited to suggestions, observations, errors, problems, and
                              defects regarding the eLife Sites (collectively &quot;Feedback&quot;) as well as
                              '.strtolower($comments)." on journal content (\"{$comments}\"). You hereby grant eLife a
                              worldwide, irrevocable, perpetual, royalty-free, transferable, and sub-licensable,
                              non-exclusive right to use, copy, modify, distribute, display, perform, create
                              derivative works from and otherwise exploit all such Feedback and {$comments}.
                              All such Feedback and {$comments} shall comply with these Terms and Conditions
                              generally and in particular the restrictions in Impermissible Uses above. In
                              addition, content provided by authors for submission shall be subject to the
                              Author Guide and Policies.")
                )),
                ArticleSection::basic('Infringement', 3, $this->render(
                    new Paragraph('If you believe that your work has been copied in a way that constitutes
                              copyright infringement or your intellectual property rights have otherwise
                              been violated, please notify <a href="mailto:info@elifesciences.org">info@elifesciences.org</a>.')
                )),
                ArticleSection::basic('Links to Other Websites and Resources', 3, $this->render(
                    new Paragraph('The eLife Sites may provide links to other websites or resources. Because
                              eLife has no control over such sites and resources, you acknowledge and agree
                              that eLife is not responsible for the availability of such external sites or
                              resources, and does not endorse and is not responsible or liable for any
                              materials on or available from such sites or resources. Your business dealings
                              with any such external sites or resources, including payment and delivery of
                              related goods or services, and any warranties, conditions or other terms
                              associated with such dealings, are solely between you and such third party.
                              You further acknowledge and agree that the eLife Parties shall not be
                              responsible or liable, directly or indirectly, for any damage or loss caused
                              or alleged to be caused by or in connection with use of or reliance on any
                              content, goods, or services available on or through any such site or
                              resource.')
                )),
                ArticleSection::basic('Modifications to eLife Site and Terms and Conditions', 3, $this->render(
                    new Paragraph('eLife reserves the right to modify or discontinue, temporarily or
                              permanently, any of the eLife Sites (or any part thereof) with or without
                              notice at any time. eLife reserves the right to change these Terms and
                              Conditions at any time by posting changes online. You are responsible for
                              reviewing regularly information posted online to obtain timely notice of such
                              changes. Your continued use of the eLife Sites after changes are posted
                              constitutes your acceptance of this agreement as modified by the posted
                              changes.')
                )),
                ArticleSection::basic('Additional Terms for Certain Features', 3, $this->render(
                    new Paragraph('Certain parts of the eLife Sites may be subject to registration and
                              additional terms and conditions which will be made available for you to read
                              at the time of registration, for example, if you register to use certain
                              features or submit articles. Those terms and conditions are incorporated
                              herein. If there is any conflict between these Terms and Conditions and/or
                              specific terms and conditions appearing elsewhere on the eLife Sites relating
                              to specific material then the latter shall prevail. When registering, you may
                              be required to select a password. You are solely responsible for maintaining
                              the confidentiality of your password, and any activity taken under your
                              password. You are required to notify eLife immediately of any known or
                              suspected unauthorized use of your account or password and take reasonable
                              steps to stop such use.')
                )),
                ArticleSection::basic('General', 3, $this->render(
                    new Paragraph('Any forbearance or indulgence by eLife in enforcing any provision in these
                              Terms and Conditions shall not affect eLife&apos;s right of enforcement and any
                              waiver of any breach shall not operate as a waiver of any subsequent or
                              continuing breach. If any of these Terms and Conditions should be determined
                              to be illegal, invalid or otherwise unenforceable by reason of the laws of any
                              state or country in which these Terms and Conditions are intended to be
                              effective, then to the extent and within the jurisdiction which that Term or
                              Condition is illegal, invalid or unenforceable, it shall be severed and
                              deleted from the clause and the remaining terms and conditions shall survive,
                              remain in full force and effect and continue to be binding and enforceable.
                              These Terms and Conditions shall be governed by and construed in accordance
                              with the laws of New York, without regard to its conflict of law principles.
                              Disputes arising here from shall be exclusively subject to the jurisdiction of
                              the courts of New York. If there is any conflict between these Terms and
                              Conditions and/or specific terms appearing elsewhere on the eLife Sites
                              relating to specific material then the latter shall prevail.')
                )),
                ArticleSection::basic('Questions and Comments', 3, $this->render(
                    new Paragraph('If you have any questions or comments about these Terms and Conditions,
                              please contact <a href="mailto:info@elifesciences.org">info@elifesciences.org</a>')
                ))
            )),
            ArticleSection::basic('Privacy Policy', 2, $this->render(
                new Paragraph('If you have questions about deleting or correcting your personal data please <a href="https://elifesciences.org/contact/">contact us</a>.
                        eLife Sciences Publications, Ltd. (&ldquo;eLife&rdquo;) operates a Website at <a href="https://elifesciences.org/">elifesciences.org</a>. It is eLife&rsquo;s policy
                        to respect your privacy regarding any information we may collect while operating our Web sites.'),
                ArticleSection::basic('Website visitors', 3, $this->render(
                    new Paragraph("Like most Website operators, eLife collects non-personally identifying information of the sort that Web browsers and servers typically make available, such as the browser type, language preference, referring site, and the date and time of each visitor request. eLife&rsquo;s purpose in collecting non-personally identifying information is to better understand how eLife&rsquo;s visitors use its Website. From time to time, eLife may release non-personally identifying information in the aggregate, e.g., by publishing a report on trends in the usage of its Website. eLife also collects potentially personally identifying information like Internet Protocol (IP) addresses for logged in users and for users leaving {$comments} on our site. eLife only discloses logged in user and commenter IP addresses under the same circumstances that it uses and discloses personally identifying information as described below, except that commenter IP addresses are visible and disclosed to the administrator.")
                )),
                ArticleSection::basic('Gathering of personally identifying information', 3, $this->render(
                    new Paragraph('Certain visitors to eLife&rsquo;s Websites choose to interact with eLife in ways that require eLife to gather personally identifying information. The amount and type of information that eLife gathers depends on the nature of the interaction. For example, eLife operates a permission-based email communication system, which allows us to collect personal information and send emails to people who want to receive them. In each case, eLife collects such information only insofar as is necessary or appropriate to fulfil the purpose of the visitor&rsquo;s interaction with eLife. eLife does not disclose personally identifying information other than as described below. And visitors can always refuse to supply personally identifying information, with the caveat that it may prevent them from engaging in certain Website-related activities.')
                )),
                ArticleSection::basic('Aggregated statistics', 3, $this->render(
                    new Paragraph('eLife may collect statistics about the behavior of visitors to its Websites. eLife may display this information publicly or provide it to others. However, eLife does not disclose personally identifying information other than as described below.')
                )),
                ArticleSection::basic('Protection of certain personally identifying information', 3, $this->render(
                    new Paragraph('eLife discloses potentially personally identifying and personally identifying information only to those of its employees, contractors and affiliated organizations that (i) need to know that information in order to process it on eLife&rsquo;s behalf or to provide services available at eLife&rsquo;s Websites, and (ii) that have agreed not to disclose it to others. Some of those employees, contractors and affiliated organizations may be located outside of your home country; by using eLife&rsquo;s Websites, you consent to the transfer of such information to them. eLife will not rent or sell potentially personally identifying and personally identifying information to anyone. Other than to its employees, contractors and affiliated organizations, as described above, eLife discloses potentially personally identifying and personally identifying information only in response to a subpoena, court order or other governmental request, or when eLife believes in good faith that disclosure is reasonably necessary to protect the property or rights of eLife, third parties or the public at large. If you are a registered user of an eLife Website and have supplied your email address, eLife may occasionally send you an email to tell you about new features, solicit your feedback, or just keep you up to date with what&rsquo;s going on with eLife and our publications &ndash; based on the preferences expressed at email sign-up. eLife takes all measures reasonably necessary to protect against the unauthorized access, use, alteration or destruction of potentially personally identifying and personally identifying information.')
                )),
                ArticleSection::basic('Cookies', 3, $this->render(
                    new Paragraph('A cookie is a string of information that a Website stores on a visitor&rsquo;s computer, and that the visitor&rsquo;s browser provides to the Website each time the visitor returns. eLife uses cookies to help eLife identify and track visitors, their usage of eLife Website, and their Website access preferences. eLife visitors who do not wish to have cookies placed on their computers should set their browsers to refuse cookies before using eLife&rsquo;s Websites, with the drawback that certain features of eLife&rsquo;s Websites may not function properly without the aid of cookies. You can modify your browser settings to notify you each time a cookie is sent to it and you can decide whether or not to accept. Alternatively, you can set your browser to refuse all cookies. Cookies that have already been set can be deleted at any time using your browser settings. The independent website at <a href="https://www.aboutcookies.org/">www.aboutcookies.org</a> contains comprehensive information on how to modify your browser settings.')
                )),
                ArticleSection::basic('Business transfers', 3, $this->render(
                    new Paragraph('If eLife, or substantially all of its assets, were acquired, or in the unlikely event that eLife goes out of business or enters bankruptcy, user information will be one of the assets that is likely to be transferred to, or acquired by, a third party. You acknowledge that such transfers may occur, and you consent to eLife&#39;s transferring your user information to any acquirer of eLife and their continuing to use your personal information as set forth in this policy.')
                )),
                ArticleSection::basic('Privacy policy changes', 3, $this->render(
                    new Paragraph('Although most changes are likely to be minor, eLife may change its Privacy Policy from time to time, and at eLife&rsquo;s sole discretion. eLife encourages visitors to frequently check this page for any changes to its Privacy Policy. Your continued use of this site after any change in this Privacy Policy will constitute your acceptance of such change. <i>First published March 30, 2012.</i> Change log:'),

                    Listing::unordered([
                        'Added para&#39;s 2-3 on cookies (5 Dec, 12)',
                        'Removed section on advertisers (3 Apr, 12)',
                    ], 'bullet')
                )),
                ArticleSection::basic('Privacy policy attribution and reuse', 3, $this->render(
                    new Paragraph('This policy is based on the one developed by <a href="https://automattic.com/privacy/">Automattic.com</a> and made available under a <a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-SA license</a>.')
                ))
            )),
        ];

        return new Response($this->get('templating')->render('::terms.html.twig', $arguments));
    }
}
