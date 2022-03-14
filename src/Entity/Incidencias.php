<?php

namespace App\Entity;

use App\Repository\IncidenciasRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IncidenciasRepository::class)]
class Incidencias
{
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $titulo;

    #[ORM\Column(type: 'datetime')]
    private $fecha_creacion;

    #[ORM\Column(type: 'string')]
    private $estado;

    #[ORM\ManyToOne(targetEntity: Clientes::class, inversedBy: 'incidencias')]
    #[ORM\JoinColumn(nullable: false)]
    private $cliente;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'incidencias')]
    #[ORM\JoinColumn(nullable: false)]
    private $usuario;

    public function __construct()
    {
        $this->usuario = new ArrayCollection();
    }
    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getFechaCreacion(): ?\DateTimeInterface
    {
        return $this->fecha_creacion;
    }

    public function setFechaCreacion(\DateTimeInterface $fecha_creacion): self
    {
        $this->fecha_creacion = $fecha_creacion;

        return $this;
    }
    
    public function getEstado(): ?string
    {
        return $this->estado;
    }
    
     public function setEstado(string $estado): self
    {
        $this->estado = $estado;
        return $this;
    }

     public function getCliente(): ?Clientes
     {
         return $this->cliente;
     }

     public function setCliente(?Clientes $cliente): self
     {
         $this->cliente = $cliente;

         return $this;
     }

     public function getUsuario(): ?User
     {
         return $this->usuario;
     }

     public function setUsuario(?User $usuario): self {
         $this->usuario = $usuario;
         
         return $this;
     }


}
