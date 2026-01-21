<?php

class CreateMultiImagesDTO
{
    /**
     * @var CreateImageDTO[]
     */
   
    #[Assert\NotNull]
    #[Assert\All([new Assert\Type(CreateImageDTO::class)])]
    public array $images = [];
}