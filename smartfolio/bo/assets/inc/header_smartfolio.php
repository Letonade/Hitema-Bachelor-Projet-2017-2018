<header id="header">
    <nav>
        <ul>
            <li>
                <a href="../">
                    <i class="fas fa-arrow-circle-left"></i>
                    Retour
                </a>
            </li>
            <li>
                <a href="index.php">
                    <i class="far fa-list-alt"></i>
                    Portfolios
                </a>
            </li>
            <?php
            if (User::AmIManager()) {
                ?>
                <li>
                    <a href="#">
                        <i class="fas fa-users"></i>
                        Utilisateurs
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="agents.php">
                                <i class="fas fa-users"></i>
                                Agents
                            </a>
                        </li>
                        <li>
                            <a href="clients.php">
                                <i class="far fa-address-book"></i>
                                Clients
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="nv_port.php">
                        <i class="fas fa-plus-circle"></i>
                        Nouveau portfolio
                    </a>
                </li>
                <?php
            }
            ?>
            <li>
                <a href="#">
                    <i class="far fa-user-circle"></i>
                    <?php echo $_SESSION['user']['name']; ?>
                </a>
                <ul class="submenu">
                    <li>
                        <a href="profil.php">
                            <i class="far fa-user"></i>
                            Profil
                        </a>
                    </li>
                    <li>
                        <a href="../?logout">
                            <i class="fas fa-sign-out-alt"></i>
                            Déconnexion
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
</header>