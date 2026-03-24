<?php

namespace Tests\Feature;

use App\Core\Services\Print\PrintConfigLoaderService;
use Tests\TestCase;

class PrintConfigLoaderServiceTest extends TestCase
{
    public function test_loader_reads_canonical_printable_registry(): void
    {
        config()->set('print.papers.a4-portrait', ['label' => 'A4 Portrait']);
        config()->set('printables.gso_air.default_paper', 'a4-portrait');
        config()->set('printables.gso_air.allowed_papers', ['a4-portrait']);
        config()->set('printables.gso_air.profiles.a4-portrait', ['orientation' => 'portrait']);

        $loader = new PrintConfigLoaderService();

        $this->assertSame('a4-portrait', $loader->defaultPaper('gso_air'));
        $this->assertSame(['a4-portrait'], $loader->allowedPapers('gso_air'));
        $this->assertSame('A4 Portrait', $loader->resolvePaperProfile('gso_air', null)['label']);
    }

    public function test_loader_allows_legacy_print_modules_overrides_for_backward_compatibility(): void
    {
        config()->set('printables', []);
        config()->set('print.papers.a4-portrait', ['label' => 'A4 Portrait']);
        config()->set('print.papers.letter-portrait', ['label' => 'Letter Portrait']);

        config()->set('print.modules.audit_logs.default_paper', 'letter-portrait');
        config()->set('print.modules.audit_logs.allowed_papers', ['letter-portrait']);
        config()->set('print.modules.audit_logs.profiles.letter-portrait', ['orientation' => 'landscape']);

        $loader = new PrintConfigLoaderService();
        $resolved = $loader->resolvePaperProfile('audit_logs', null);

        $this->assertSame('letter-portrait', $loader->defaultPaper('audit_logs'));
        $this->assertSame(['letter-portrait'], $loader->allowedPapers('audit_logs'));
        $this->assertSame('Letter Portrait', $resolved['label']);
        $this->assertSame('landscape', $resolved['orientation']);
    }
}
