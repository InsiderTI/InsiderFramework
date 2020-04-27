<?php

namespace Modules\InsiderFramework\Sagacious\Lib\Interfaces;

use Modules\InsiderFramework\Sagacious\Lib\SgsComponentStates;

interface ISgsComponent
{
    public function __construct(string $componentId, string $app);

    public function initialize(): void;

    public function renderCloseTag(): string;

    public function rawComponent();

    public function renderComponent(): void;

    public function getStates(): SgsComponentStates;
}
