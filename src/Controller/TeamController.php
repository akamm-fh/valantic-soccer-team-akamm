<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace App\Controller;

use Pimcore\Model\DataObject\Team;
use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TeamController extends FrontendController
{
    public function listAction(Request $request): Response
    {
        $teams = new Team\Listing();
        $teams->setOrderKey('name');
        $teams->setOrder('ASC');

        return $this->render('list.html.twig', [
            'teams' => $teams,
        ]);
    }
     
    public function detailAction(Request $request, string $slug): Response
    {
        $team = Team::getByPath('/Teams/'. $slug);

        return $this->render('detail.html.twig', [
            'team' => $team
        ]);
    }
}
