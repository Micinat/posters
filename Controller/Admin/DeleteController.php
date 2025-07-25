<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Posters\Controller\Admin;


use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Posters\Entity\Event\PosterEvent;
use BaksDev\Posters\Entity\Poster;
use BaksDev\Posters\UseCase\Admin\Delete\DeletePosterDTO;
use BaksDev\Posters\UseCase\Admin\Delete\DeletePosterForm;
use BaksDev\Posters\UseCase\Admin\Delete\DeletePosterHandler;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
#[RoleSecurity('ROLE_POSTER_DELETE')]
final class DeleteController extends AbstractController
{
    #[Route('/admin/poster/delete/{id}', name: 'admin.delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        #[MapEntity] PosterEvent $PosterEvent,
        DeletePosterHandler $PosterDeleteHandler,
    ): Response
    {
        $PosterDeleteDTO = new DeletePosterDTO();
        $PosterEvent->getDto($PosterDeleteDTO);
        $form = $this->createForm(DeletePosterForm::class, $PosterDeleteDTO, [
            'action' => $this->generateUrl('posters:admin.delete', ['id' => $PosterDeleteDTO->getEvent()]),
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('delete_poster'))
        {
            $handle = $PosterDeleteHandler->handle($PosterDeleteDTO);

            $this->addFlash
            (
                'page.delete',
                $handle instanceof Poster ? 'success.delete' : 'danger.delete',
                'posters.admin',
                $handle
            );

            return $handle instanceof Poster ? $this->redirectToRoute('posters:admin.index') : $this->redirectToReferer();
        }

        return $this->render([
            'form' => $form->createView(),
            'title' => $PosterEvent->getTitle(),
        ]);
    }
}
