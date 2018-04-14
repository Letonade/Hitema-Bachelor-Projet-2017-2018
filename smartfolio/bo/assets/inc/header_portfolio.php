<header id="header">
    <nav>
        <ul>
            <li>
                <a href="index.php">
                    <i class="fas fa-arrow-circle-left"></i>
                    Retour
                </a>
            </li>
            <li>
                <a <?php App::SetLink('portfolio.php?port=' . $_GET['port']); ?>>
                    <i class="fab fa-bitcoin"></i>
                    Portefeuille
                </a>
            </li>
            <li>
                <a <?php App::SetLink('rapport.php?port=' . $_GET['port']); ?>>
                    <i class="fas fa-chart-area"></i>
                    Rapports
                </a>
            </li>
            <li>
                <a <?php App::SetLink('port_settings.php?port=' . $_GET['port']); ?>>
                    <i class="fas fa-cog"></i>
                    Paramètres
                </a>
            </li>
        </ul>
    </nav>
</header>