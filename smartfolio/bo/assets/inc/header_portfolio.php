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
                <a href="portfolio.php?port=<?php echo $_GET['port']; ?>">
                    <i class="fab fa-bitcoin"></i>
                    Portefeuille
                </a>
            </li>
            <li>
                <a href="rapport.php?port=<?php echo $_GET['port']; ?>">
                    <i class="fas fa-chart-area"></i>
                    Rapports
                </a>
            </li>
            <li>
                <a href="settings.php?port=<?php echo $_GET['port']; ?>">
                    <i class="fas fa-cog"></i>
                    Paramètres
                </a>
            </li>
        </ul>
    </nav>
</header>