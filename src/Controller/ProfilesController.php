<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Model\Profile;
use eLife\Patterns\ViewModel\ContentHeaderProfile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ProfilesController extends Controller
{
    public function profileAction(Request $request, string $id) : Response
    {
        if (!$this->get('security.authorization_checker')->isGranted('FEATURE_CAN_VIEW_PROFILES')) {
            throw new NotFoundHttpException('Not found');
        }

        $profile = $this->get('elife.api_sdk.profiles')
            ->get($id)
            ->otherwise($this->mightNotExist());

        $arguments = $this->defaultPageArguments($request, $profile);

        $arguments['title'] = $profile
            ->then(function (Profile $profile) {
                return $profile->getDetails()->getPreferredName();
            });

        $arguments['profile'] = $profile;

        $arguments['contentHeader'] = $arguments['profile']
            ->then(function (Profile $profile) use ($arguments) {
                if ($profile->getId() === $arguments['user']->getUsername()) {
                    $isUser = true;
                }

                return $this->convertTo($profile, ContentHeaderProfile::class, ['isUser' => $isUser ?? false]);
            });

        return new Response($this->get('templating')->render('::profile.html.twig', $arguments));
    }
}
