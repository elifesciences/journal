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

        $arguments['title'] = 'Terms and conditions';

        $arguments['contentHeader'] = new ContentHeader($arguments['title']);

        $arguments['body'] = [
            ArticleSection::basic($this->render(
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
            ), 'Welcome and Consent to Terms', 2),
            ArticleSection::basic($this->render(
                new Paragraph('The eLife Sites, the software ("eLife Software"), application programming
                          interfaces ("eLife APIs"), content and trademarks used on or in connection
                          with the eLife Sites are owned by eLife or its licensors, and are subject to
                          US and international intellectual property rights and laws. Nothing contained
                          herein shall be construed as conferring by implication, or otherwise any
                          license or right under any trademark, copyright or patent of eLife or any
                          other third party and all rights are reserved, except as explicitly stated in
                          these Terms and Conditions.')
            ), 'Ownership', 2),
            ArticleSection::basic($this->render(
                new Paragraph('Unless otherwise indicated, the articles and journal content published by
                          eLife on the eLife Sites are licensed under a <a
                            href="https://creativecommons.org/licenses/by/4.0/">Creative Commons
                            Attribution license</a> (also known as a CC-BY license). This means that you
                          are free to use, reproduce and distribute the articles and related content
                          (unless otherwise noted), for commercial and noncommercial purposes, subject
                          to citation of the original source in accordance with the CC-BY license.')
            ), 'License to Use Journal Articles and Related Content', 2),
            ArticleSection::basic($this->render(
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
            ), 'Impermissible Uses', 2),
            ArticleSection::basic($this->render(
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
            ), 'Suspension of Use / Violation of these Terms and Conditions', 2),
            ArticleSection::basic($this->render(
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
            ), 'Disclaimer of Warranty', 2),
            ArticleSection::basic($this->render(
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
            ), 'Limitation of Liability', 2),
            ArticleSection::basic($this->render(
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
            ), 'Indemnity', 2),
            ArticleSection::basic($this->render(
                new Paragraph('In the course of using the eLife Sites, you may provide eLife with feedback,
                              including but not limited to suggestions, observations, errors, problems, and
                              defects regarding the eLife Sites (collectively &quot;Feedback&quot;) as well as
                              annotations on journal content (Annotations). You hereby grant eLife a
                              worldwide, irrevocable, perpetual, royalty-free, transferable, and sub-licensable,
                              non-exclusive right to use, copy, modify, distribute, display, perform, create
                              derivative works from and otherwise exploit all such Feedback and Annotations.
                              All such Feedback and Annotations shall comply with these Terms and Conditions
                              generally and in particular the restrictions in Impermissible Uses above. In
                              addition, content provided by authors for submission shall be subject to the
                              Author Guide and Policies.')
            ), 'Interactive Features', 2),
            ArticleSection::basic($this->render(
                new Paragraph('If you believe that your work has been copied in a way that constitutes
                              copyright infringement or your intellectual property rights have otherwise
                              been violated, please notify <a href="mailto:info@elifesciences.org">info@elifesciences.org</a>.')
            ), 'Infringement', 2),
            ArticleSection::basic($this->render(
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
            ), 'Links to Other Websites and Resources', 2),
            ArticleSection::basic($this->render(
                new Paragraph('eLife reserves the right to modify or discontinue, temporarily or
                              permanently, any of the eLife Sites (or any part thereof) with or without
                              notice at any time. eLife reserves the right to change these Terms and
                              Conditions at any time by posting changes online. You are responsible for
                              reviewing regularly information posted online to obtain timely notice of such
                              changes. Your continued use of the eLife Sites after changes are posted
                              constitutes your acceptance of this agreement as modified by the posted
                              changes.')
            ), 'Modifications to eLife Site and Terms and Conditions', 2),
            ArticleSection::basic($this->render(
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
            ), 'Additional Terms for Certain Features', 2),
            ArticleSection::basic($this->render(
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
            ), 'General', 2),
            ArticleSection::basic($this->render(
                new Paragraph('If you have any questions or comments about these Terms and Conditions,
                              please contact <a href="mailto:info@elifesciences.org">info@elifesciences.org</a>')
            ), 'Questions and Comments', 2),
        ];

        return new Response($this->get('templating')->render('::terms.html.twig', $arguments));
    }
}
