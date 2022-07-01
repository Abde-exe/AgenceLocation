<?php

namespace App\Controller;

use App\Entity\Vehicule;
use App\Form\VehiculeType;
use App\Repository\VehiculeRepository;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class VehiculeController extends AbstractController
{
    #[Route('/vehicule/{id}', name: 'app_vehicule')]
    public function index(ManagerRegistry $doctrine, $id)
    {
        $vehicule = $doctrine->getRepository(Vehicule::class)->find($id);
        dd($vehicule);
        return $this->render("vehicule/vehicule.html.twig", [
            'vehicule' => $vehicule
        ]);
    }


    #[Route('/vehicules', name: 'all_vehicules')]
    public function getAllVehicules(ManagerRegistry $doctrine)
    {
        $vehicules = $doctrine->getRepository(Vehicule::class)->findAll();
        return $this->render('vehicule/AllVehicules.html.twig', [
            'vehicules' => $vehicules,
        ]);
    }
    #[Route('/admin/vehicules', name: 'admin_all_vehicules')]
    public function adminAllVehicules(ManagerRegistry $doctrine)
    {
        $vehicules = $doctrine->getRepository(Vehicule::class)->findAll();
        return $this->render('vehicule/admin/AllVehicules.html.twig', [
            'vehicules' => $vehicules,
        ]);
    }
    #[Route('/admin/new-vehicule', name: 'admin_add_vehicule')]
    public function ajout(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger)
    {

        if (!$this->isGranted('IS_AUTHENTICATE_FULLY')) {
            $this->addFlash('error', "Veuillez vous connecter pour accéder à cette page");
            return $this->redirectToRoute('app_login');
        }
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', "Vous n'etes pas autorisé");
            return $this->redirectToRoute('app_home');
        }
        $vehicule = new Vehicule();

        $form = $this->createForm(VehiculeType::class, $vehicule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // on recupere l'image depuis le formulaire
            $file = $form->get('photo')->getData();
            //dd($file);
            //dd($vehicule);
            // le slug permet de modifier une chaine de caractéres : mot clé => mot-cle
            $fileName = $slugger->slug($vehicule->getTitre()) . uniqid() . '.' . $file->guessExtension();

            try {
                // on deplace le fichier image recuperé depuis le formulaire dans le dossier parametré dans la partie Parameters du fichier config/service.yaml, avec pour nom $fileName
                $file->move($this->getParameter('photos_vehicules'),  $fileName);
            } catch (FileException $e) {
                // gérer les exeptions en cas d'erreur durant l'upload
            }

            $vehicule->setPhoto($fileName);

            $vehicule->setDateEnregistrement(new DateTime("now"));

            $manager = $doctrine->getManager();
            $manager->persist($vehicule);
            $manager->flush();
            $this->addFlash("success", "Vehicule a bien été ajouté");
            return $this->redirectToRoute("app_home");
        }

        return $this->render("vehicule/admin/formulaire.html.twig", [
            "formVehicule" => $form->createView()
        ]);
    }
    #[Route('/admin/update_vehicule/{id<\d+>}', name: 'admin_update_vehicule')]
    public function update(ManagerRegistry $doctrine, $id, Request $request, SluggerInterface $slugger)
    {
        if (!$this->isGranted('IS_AUTHENTICATE_FULLY')) {
            $this->addFlash('error', "Veuillez vous connecter pour accéder à cette page");
            return $this->redirectToRoute('app_login');
        }
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', "Vous n'etes pas autorisé");
            return $this->redirectToRoute('app_home');
        }
        $vehicule = $doctrine->getRepository(Vehicule::class)->find($id);

        $form = $this->createForm(VehiculeType::class, $vehicule);
        $form->handleRequest($request);

        // on stock l'image du vehicule à mettre à jour
        $image = $vehicule->getPhoto();

        if ($form->isSubmitted() && $form->isValid()) {
            // si une image a bien été ajouté au formulaire
            if ($form->get('photo')->getData()) {
                // on recupere l'image du formulaire
                $imageFile = $form->get('photo')->getData();

                //on crée un nouveau nom pour l'image
                $fileName = $slugger->slug($vehicule->getTitre()) . uniqid() . '.' . $imageFile->guessExtension();

                //on deplace l'image dans le dossier parametré dans service.yaml
                try {
                    $imageFile->move($this->getParameter('photos_vehicules'), $fileName);
                } catch (FileException $e) {
                    // gestion des erreur upload
                }
                $vehicule->setPhoto($fileName);
            }


            $manager = $doctrine->getManager();
            $manager->persist($vehicule);
            $manager->flush();
            $this->addFlash("success", "Vehicule a bien été modifié");

            return $this->redirectToRoute('admin_all_vehicules');
        }

        return $this->render("vehicule/admin/formulaire.html.twig", [
            'formVehicule' => $form->createView()
        ]);
    }
    #[Route('/delete-vehicule/{id}', name: 'delete_vehicule')]
    public function deleteVehicule(ManagerRegistry $doctrine,  $id, VehiculeRepository $repo)
    {
        // $vehicule = $doctrine->getRepository(Vehicule::class)->find($id);
        // $manager = $doctrine->getManager();
        // $manager->remove($vehicule);
        // $manager->flush();

        if (!$this->isGranted('IS_AUTHENTICATE_FULLY')) {
            $this->addFlash('error', "Veuillez vous connecter pour accéder à cette page");
            return $this->redirectToRoute('app_login');
        }
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', "Vous n'etes pas autorisé");
            return $this->redirectToRoute('app_home');
        }

        $vehicule = $repo->find($id);
        $repo->remove($vehicule, 1);
        $this->addFlash("success", "Vehicule a bien été supprimé");

        return $this->redirectToRoute("admin_all_vehicules");
    }
}
