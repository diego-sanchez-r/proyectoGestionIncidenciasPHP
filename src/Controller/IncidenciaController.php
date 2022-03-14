<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Incidencias;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Clientes;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;



class IncidenciaController extends AbstractController
{
    #[Route('/incidencias', name: 'app_incidencia')]
    public function index(ManagerRegistry $doctrine): Response{
        if($this->getUser() === null){
            return $this->redirectToRoute("login");
        }
        $repositorio = $doctrine->getRepository(Incidencias::class);
        $incidencias = $repositorio->findBy(
           [],
           ["fecha_creacion" => "DESC"]     
        );
        return $this->render('incidencia/listaIncidencias.html.twig', [
            'controller_name' => 'Incidencias',
            'todosLasIncidencias' => $incidencias,
        ]);
    }
    
    
    
    /**
     * @Route("/incidencia/{id<\d+>}",name="ver_incidencia")
    */
    public function ver(Incidencias $incidenciaver, Request $request, ManagerRegistry $doctrine): Response {
    if($this->getUser() === null){
        return $this->redirectToRoute("login");
    }
    $repositorio = $doctrine->getRepository(Incidencias::class);
    $id = $request->get('id');
    $incidenciaver = $repositorio->find($id);

    return $this->render('incidencia/verIncidencia.html.twig', [
        'incidenciaSeleccionada' => $incidenciaver,
    ]);

    }
    
     /**
     *@Route("/incidencia/borrar/{id<\d+>}",name="borrar_incidencia")
     */
    public function borrar(Incidencias $incidencia, ManagerRegistry $doctrine): Response{
        if($this->getUser() === null){
            return $this->redirectToRoute("login");
        }
        
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_REMEMBERED')) {
            $this->addFlash("aviso", "Debe de volver a iniciar sesión para borrar una incidencia.");
            return $this->redirectToRoute('app_incidencia');
        }
        
        $em = $doctrine->getManager();
        $em->remove($incidencia);
        $em->flush();
        
        $this->addFlash("aviso", "Incidencia borrada");
        return $this->redirectToRoute("app_cliente");
    }
    
    
    /**
     * @Route("/incidencia/editar/{id}", name="editar_incidencia")
     */
        public function editar(Incidencias $incidencia, Request $request, ManagerRegistry $doctrine): Response {
                if($this->getUser() === null){
                    return $this->redirectToRoute("login");
                }
                
                
            $securityContext = $this->container->get('security.authorization_checker');
            if ($securityContext->isGranted('IS_REMEMBERED')) {
                $this->addFlash("aviso", "Debe de volver a iniciar sesión para editar una incidencia.");
                return $this->redirectToRoute('app_incidencia');
            }
            
            if ($request->isMethod('POST')) {

                $titulo = $request->request->get('titulo');
                $estado = $request->request->get('estado');

                $incidencia->setTitulo($titulo);
                $incidencia->setEstado($estado);

                if (empty($titulo) || empty($estado)) {
                    $this->addFlash('aviso', "Debe completar tanto el título como el estado");
                    return $this->render("incidencia/editarIncidencia.html.twig", ['incidencia' => $incidencia]);
                } else {
                    $em = $doctrine->getManager();
                    $em->flush();

                    $this->addFlash('aviso', "Incidencia editada");
                    return $this->redirectToRoute("app_cliente");
                }

            } else {
                return $this->render("incidencia/editarIncidencia.html.twig", ['incidencia' => $incidencia]);
            }
        }
        
            /**
            * @Route("/incidencia/editar2/{id<\d+>}", name="editar_incidencia2")
            */
        public function editar2(Incidencias $incidenciaver, Request $request, ManagerRegistry $doctrine): Response{
             if($this->getUser() === null){
                    return $this->redirectToRoute("login");
                }
                
            $securityContext = $this->container->get('security.authorization_checker');
            if ($securityContext->isGranted('IS_REMEMBERED')) {
                $this->addFlash("aviso", "Debe de volver a iniciar sesión para editar una incidencia.");
                return $this->redirectToRoute('app_incidencia');
            }
            
            //FORMULARIO
            $incidencia = new Incidencias();
            $form = $this->createFormBuilder($incidenciaver)
                ->add('Titulo', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor, indique el titulo de la incidencia.',
                    ]),
                ],
                'data' => $incidenciaver->getTitulo(),
                'attr' => array(
                    'placeholder' => 'Ingrese el Titulo ',
                    'class' => 'controls'
                )
            ])
                ->add('Estado',ChoiceType::class, [
                        'choices'  => [ 
                        $incidenciaver->getEstado() => $incidenciaver->getEstado(),
                        'Iniciada' => "Iniciada",
                        'En proceso' => "En proceso",
                        'Resuelta' => "Resuelta",
                    ],
                    'attr' => array(
                    'class' => 'controls'
                )
                ] )
                ->add('submit', SubmitType::class, array(
                    'label' => 'Modificar Incidencia',
                    'attr'  => array('class' => 'botons')
                ))
                ->getForm();
                ;
                $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $titulo = $form->get('Titulo')->getData();
                $estado = $form->get('Estado')->getData();
            
                $incidenciaver->setTitulo($titulo);
                $incidenciaver->setEstado($estado);
                $incidenciaver->setCliente($incidenciaver->getCliente());
                $incidenciaver->setUsuario($incidenciaver->getUsuario());
                $incidenciaver->setFechaCreacion($incidenciaver->getFechaCreacion());
                $em = $doctrine->getManager();
                $em->flush();

                $this->addFlash("aviso", "Incidencia editada");
            return $this->redirectToRoute("app_cliente");
            }
            return $this->renderForm('incidencia/editarForm.html.twig', ['form_post'=>$form]);
            }
        
    /**
     * @Route("/incidencia/insertar/{id<\d+>}", name="insertar_incidencia")
    */
    public function insertar(Clientes $cliente,Request $request, ManagerRegistry $doctrine): Response {
        if($this->getUser() === null){
            return $this->redirectToRoute("login");
        }
      if ($request->isMethod('POST')) {
            $titulo = $request->request->get('titulo');
            $estado = $request->request->get('estado');
            $usuarioIn = $request->request->get('usuario');
            $clienteIn = $request->request->get('cliente');
            $incidencia = new Incidencias();
            $incidencia->setTitulo($titulo);
            $incidencia->setEstado($estado);
            $repositorio2 = $doctrine->getRepository(User::class);
            $usuariover = $repositorio2->find($usuarioIn);
            $incidencia->setUsuario($usuariover);
            $repositorio = $doctrine->getRepository(Clientes::class);
            $clientever = $repositorio->find($clienteIn);
            $incidencia->setCliente($clientever);
            $incidencia->setFechaCreacion(new \DateTime());
            $em = $doctrine->getManager();
            $em->persist($incidencia);
            $em->flush();
            
            $this->addFlash("aviso", "Incidencia Insertada");
            return $this->redirectToRoute("app_cliente");
        } else {
            return $this->render("incidencia/insertarIncidencia.html.twig", ['cliente' => $cliente]);
        }
    }
    
    /**
     * @Route("/incidencia2/insertar2", name="insertar_incidencia2")
    */
    public function insertar2(Request $request, ManagerRegistry $doctrine): Response {
            if($this->getUser() === null){
                return $this->redirectToRoute("login");
            }
         $repositorio = $doctrine->getRepository(Clientes::class);
      if ($request->isMethod('POST')) {
            $titulo = $request->request->get('titulo');
            $estado = $request->request->get('estado');
            $usuarioIn = $request->request->get('usuario');
            $clienteIn = $request->request->get('cliente');
            $incidencia = new Incidencias();
            $incidencia->setTitulo($titulo);
            $incidencia->setEstado($estado);
            $repositorio2 = $doctrine->getRepository(User::class);
            $usuariover = $repositorio2->find($usuarioIn);
            $incidencia->setUsuario($usuariover);
            $clientever = $repositorio->find($clienteIn);
            $incidencia->setCliente($clientever);
            $incidencia->setFechaCreacion(new \DateTime());
            $em = $doctrine->getManager();
            $em->persist($incidencia);
            $em->flush();
            
            $this->addFlash("aviso", "Incidencia Insertada");
            return $this->redirectToRoute("app_cliente");
        } else {
            $clientes = $repositorio->findAll();
            return $this->render("incidencia/insertarIncidencia2.html.twig", ['clientes' => $clientes]);
        }
    }
    
    
    /**
     * @Route("/incidencia/insertar3/{id<\d+>}", name="insertar_incidencia3")
    */
    public function insertar3(Clientes $cliente,Request $request, ManagerRegistry $doctrine): Response {
        if($this->getUser() === null){
            return $this->redirectToRoute("login");
        }
            $incidencia = new Incidencias();
            $form = $this->createFormBuilder($incidencia)
                ->add('Titulo', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor, indique el titulo de la incidencia.',
                    ]),
                ],
                'attr' => array(
                    'placeholder' => 'Ingrese el Titulo ',
                    'class' => 'controls'
                )
            ])
                ->add('Estado',ChoiceType::class, [
                        'choices'  => [ 
                        'Iniciada' => "Iniciada",
                        'En proceso' => "En proceso",
                        'Resuelta' => "Resuelta",
                    ],
                    'attr' => array(
                    'class' => 'controls'
                )
                ] )
                ->add('submit', SubmitType::class, array(
                    'label' => 'Insertar Incidencia',
                    'attr'  => array('class' => 'botons')
                ))
                ->getForm();
                ;
                $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $titulo = $form->get('Titulo')->getData();
                $estado = $form->get('Estado')->getData();
            
                $incidencia->setTitulo($titulo);
                $incidencia->setEstado($estado);
                $incidencia->setCliente($cliente);
                $incidencia->setUsuario($this->getUser());
                $incidencia->setFechaCreacion(new \DateTime());
                $em = $doctrine->getManager();
                $em->persist($incidencia);
                $em->flush();
            
            $this->addFlash("aviso", "Incidencia Insertada");
            return $this->redirectToRoute("app_cliente");
        } else {
            return $this->renderForm("incidencia/insertarFormDesdeCliente.html.twig", ['form_post' => $form]);
        }
    }
    
         /**
     * @Route("/incidencia/insertar4", name="insertar_incidencia4")
    */
    public function insertar4(Request $request, ManagerRegistry $doctrine): Response {
        if($this->getUser() === null){
            return $this->redirectToRoute("login");
        }
            $incidencia = new Incidencias();
            $form = $this->createFormBuilder($incidencia)
                ->add('Titulo', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor, indique el titulo de la incidencia.',
                    ]),
                ],
                'attr' => array(
                    'placeholder' => 'Ingrese el Titulo ',
                    'class' => 'controls'
                )
            ])
                ->add('Estado',ChoiceType::class, [
                        'choices'  => [ 
                        'Iniciada' => "Iniciada",
                        'En proceso' => "En proceso",
                        'Resuelta' => "Resuelta",
                    ],
                    'attr' => array(
                    'class' => 'controls'
                )
                ] )
                ->add('Cliente',EntityType::class, [
                       'class' => Clientes::class,
                    'choice_label' => 'nombre',
                    'choice_value' => 'id',
                    'attr' => array(
                    'class' => 'controls'
                )
                ])
                ->add('submit', SubmitType::class, array(
                    'label' => 'Insertar Incidencia',
                    'attr'  => array('class' => 'botons')
                ))
                ->getForm();
                ;
                $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $titulo = $form->get('Titulo')->getData();
                $estado = $form->get('Estado')->getData();
                $cliente = $form->get('Cliente')->getData();
            
                $incidencia->setTitulo($titulo);
                $incidencia->setEstado($estado);
                $incidencia->setCliente($cliente);
                $incidencia->setUsuario($this->getUser());
                $incidencia->setFechaCreacion(new \DateTime());
                $em = $doctrine->getManager();
                $em->persist($incidencia);
                $em->flush();
            
            $this->addFlash("aviso", "Incidencia Insertada");
            return $this->redirectToRoute("app_cliente");
        } else {
            return $this->renderForm("incidencia/insertarFormDesdeCliente.html.twig", ['form_post' => $form]);
        }
    }   
        
        
        
        
        
}
