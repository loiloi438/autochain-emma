<template>
  <div class="landing-container">
    <!-- Navbar -->
    <nav class="navbar">
      <div class="nav-content">
        <div class="logo">
          <span class="logo-icon">🔗</span>
          <span class="logo-text">AutoChain Emma+</span>
        </div>
        <ul class="nav-links">
          <li><a href="#features">Fonctionnalités</a></li>
          <li><a href="#benefits">Avantages</a></li>
          <li><RouterLink class="nav-btn primary" to="/login">Se connecter</RouterLink></li>
        </ul>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
      <div class="hero-content">
        <span class="eyebrow">🚀 Gestion de flotte intelligente</span>
        <h1>AutoChain Emma+ pour les flottes connectées</h1>
        <p>Créez une traçabilité blockchain, suivez vos interventions et automatisez les alertes depuis une interface intuitive et sécurisée.</p>

        <div class="hero-stats">
          <div class="stat-item">
            <strong>237</strong>
            <span>Véhicules suivis</span>
          </div>
          <div class="stat-item">
            <strong>4 512</strong>
            <span>Transactions certifiées</span>
          </div>
          <div class="stat-item">
            <strong>98%</strong>
            <span>D'exploitation optimale</span>
          </div>
        </div>

        <div class="hero-buttons">
          <RouterLink class="button primary" to="/login">Accéder au Dashboard</RouterLink>
          <button class="button ghost">En savoir plus</button>
        </div>

        <div class="hero-trust">
          <p>✓ Déjà adopté par les gestionnaires de flotte les plus exigeants</p>
        </div>
      </div>

      <div class="hero-visual">
        <div class="visual-container">
          <div class="blockchain-card">
            <div class="card-header">
              <span class="badge">Blockchain</span>
              <span class="icon">⛓️</span>
            </div>
            <div class="card-content">
              <div class="transaction-items">
                <div class="transaction-item">
                  <span class="dot"></span>
                  <span>Transactions immuables</span>
                </div>
                <div class="transaction-item">
                  <span class="dot"></span>
                  <span>Certifiées et horodatées</span>
                </div>
              </div>
              <div class="card-stat">
                <strong>5 125</strong>
                <span>transactions</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
      <div class="section-header">
        <h2>Fonctionnalités clés</h2>
        <p>Une solution complète pour gérer votre flotte avec confiance</p>
      </div>

      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon">🔐</div>
          <h3>Sécurité & Traçabilité</h3>
          <p>Chaque maintenance, document et assignation est horodatée sur la blockchain.</p>
          <div class="feature-benefits">
            <span>✓ Audit immuable</span>
            <span>✓ Conformité RGPD</span>
          </div>
        </div>

        <div class="feature-card">
          <div class="feature-icon">📊</div>
          <h3>Tableau de Bord Intelligent</h3>
          <p>Analysez les performances, alertes et état des véhicules en temps réel.</p>
          <div class="feature-benefits">
            <span>✓ Dashboards personnalisés</span>
            <span>✓ Alertes en temps réel</span>
          </div>
        </div>

        <div class="feature-card">
          <div class="feature-icon">💼</div>
          <h3>Gestion & Documents</h3>
          <p>Suivi complet des entretiens, assurances et contrôles techniques.</p>
          <div class="feature-benefits">
            <span>✓ Gestion documentaire</span>
            <span>✓ Rappels automatiques</span>
          </div>
        </div>

        <div class="feature-card">
          <div class="feature-icon">🔗</div>
          <h3>Intégration Wallet Native</h3>
          <p>Authentifiez-vous et signez les opérations blockchain avec MetaMask.</p>
          <div class="feature-benefits">
            <span>✓ Web3 intégré</span>
            <span>✓ Signatures cryptographiques</span>
          </div>
        </div>

        <div class="feature-card">
          <div class="feature-icon">👥</div>
          <h3>Rôles & Permissions</h3>
          <p>Adaptez les accès à chaque profil : gestionnaire, chauffeur, mécanicien.</p>
          <div class="feature-benefits">
            <span>✓ RBAC flexible</span>
            <span>✓ Audit des accès</span>
          </div>
        </div>

        <div class="feature-card">
          <div class="feature-icon">📱</div>
          <h3>Interface Responsive</h3>
          <p>Accédez à votre flotte depuis n'importe quel appareil.</p>
          <div class="feature-benefits">
            <span>✓ Mobile optimisé</span>
            <span>✓ UI/UX moderne</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Benefits Section -->
    <section id="benefits" class="benefits">
      <div class="benefits-content">
        <div class="benefits-text">
          <h2>Pourquoi AutoChain Emma+ ?</h2>
          <ul class="benefits-list">
            <li>
              <strong>Réduire les coûts</strong>
              <p>Optimisez la maintenance et identifiez les inefficacités rapidement</p>
            </li>
            <li>
              <strong>Améliorer la sécurité</strong>
              <p>Suivi en temps réel et alertes automatiques</p>
            </li>
            <li>
              <strong>Garantir la conformité</strong>
              <p>Traçabilité immuable pour les audits</p>
            </li>
            <li>
              <strong>Augmenter l'efficacité</strong>
              <p>Automatisez les processus et réduisez la saisie manuelle</p>
            </li>
          </ul>
        </div>

        <div class="benefits-stats">
          <div class="stat-box">
            <span class="stat-number">-30%</span>
            <span class="stat-desc">Réduction des coûts</span>
          </div>
          <div class="stat-box">
            <span class="stat-number">+45%</span>
            <span class="stat-desc">Gain de productivité</span>
          </div>
          <div class="stat-box">
            <span class="stat-number">100%</span>
            <span class="stat-desc">Traçabilité assurée</span>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
      <div class="cta-content">
        <h2>Prêt à optimiser votre flotte ?</h2>
        <p>Débutez dès maintenant avec notre interface intuitive et sécurisée</p>
        <div class="cta-buttons">
          <RouterLink class="button primary" to="/login">Se connecter maintenant</RouterLink>
          <button class="button ghost">Demander une démo</button>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <div class="footer-content">
        <div class="footer-section">
          <h4>AutoChain Emma+</h4>
          <p>La solution blockchain pour la gestion intelligente des flottes automobiles.</p>
        </div>
        <div class="footer-section">
          <h4>Liens</h4>
          <ul>
            <li><a href="#features">Fonctionnalités</a></li>
            <li><a href="#benefits">Avantages</a></li>
            <li><RouterLink to="/login">Connexion</RouterLink></li>
          </ul>
        </div>
        <div class="footer-section">
          <h4>Légal</h4>
          <ul>
            <li><a href="#">Mentions Légales</a></li>
            <li><a href="#">Politique de Confidentialité</a></li>
            <li><a href="#">Contact</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2026 AutoChain Emma+ - Gestion de flotte certifiée blockchain</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.landing-container {
  width: 100%;
  overflow-x: hidden;
  background-color: white;
}

/* NAVBAR */
.navbar {
  position: sticky;
  top: 0;
  z-index: 1000;
  background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8f 100%);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  padding: 0 20px;
}

.nav-content {
  max-width: 1400px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 0;
}

.logo {
  display: flex;
  align-items: center;
  gap: 8px;
  color: white;
  font-weight: 700;
  font-size: 18px;
}

.logo-icon {
  font-size: 24px;
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 24px;
  list-style: none;
  margin: 0;
  padding: 0;
}

.nav-links a {
  color: white;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  transition: opacity 0.3s;
}

.nav-links a:hover {
  opacity: 0.8;
}

.nav-btn {
  padding: 8px 16px;
  border-radius: 6px;
  border: none;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  white-space: nowrap;
}

.nav-btn.primary {
  background-color: #3b82f6;
  color: white;
  text-decoration: none;
  display: inline-block;
}

.nav-btn.primary:hover {
  background-color: #2563eb;
  transform: translateY(-2px);
}

/* HERO SECTION */
.hero {
  padding: 80px 20px;
  background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  align-items: center;
  max-width: 1400px;
  margin: 0 auto;
}

@media (max-width: 1024px) {
  .hero {
    grid-template-columns: 1fr;
    padding: 60px 20px;
  }
}

.eyebrow {
  display: inline-block;
  font-size: 13px;
  font-weight: 600;
  color: #3b82f6;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 12px;
}

.hero h1 {
  font-size: 48px;
  font-weight: 800;
  line-height: 1.2;
  color: #1f2937;
  margin: 0 0 16px 0;
}

.hero p {
  font-size: 18px;
  color: #6b7280;
  line-height: 1.6;
  margin: 0 0 32px 0;
}

.hero-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
  margin-bottom: 32px;
}

.stat-item {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.stat-item strong {
  font-size: 32px;
  color: #1e40af;
}

.stat-item span {
  font-size: 13px;
  color: #6b7280;
}

.hero-buttons {
  display: flex;
  gap: 16px;
  margin-bottom: 32px;
}

.button {
  padding: 12px 32px;
  border-radius: 8px;
  border: none;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  text-decoration: none;
  display: inline-block;
}

.button.primary {
  background-color: #3b82f6;
  color: white;
}

.button.primary:hover {
  background-color: #2563eb;
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(59, 130, 246, 0.4);
}

.button.ghost {
  background-color: transparent;
  color: #3b82f6;
  border: 2px solid #3b82f6;
}

.button.ghost:hover {
  background-color: #eff6ff;
}

.hero-trust {
  padding: 16px;
  background-color: white;
  border-radius: 8px;
  border-left: 4px solid #10b981;
}

.hero-trust p {
  margin: 0;
  color: #1f2937;
  font-size: 14px;
}

.hero-visual {
  display: flex;
  align-items: center;
  justify-content: center;
}

.visual-container {
  width: 100%;
}

.blockchain-card {
  background: white;
  border-radius: 16px;
  padding: 32px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
}

.badge {
  background-color: #dbeafe;
  color: #1e40af;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
}

.card-header .icon {
  font-size: 28px;
}

.card-content {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.transaction-items {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.transaction-item {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 14px;
  color: #4b5563;
}

.dot {
  width: 8px;
  height: 8px;
  background-color: #3b82f6;
  border-radius: 50%;
  flex-shrink: 0;
}

.card-stat {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding-top: 12px;
  border-top: 1px solid #e5e7eb;
}

.card-stat strong {
  font-size: 28px;
  color: #1e40af;
}

.card-stat span {
  font-size: 12px;
  color: #6b7280;
}

/* FEATURES SECTION */
.section-header {
  text-align: center;
  margin-bottom: 60px;
}

.section-header h2 {
  font-size: 40px;
  font-weight: 800;
  color: #1f2937;
  margin: 0 0 12px 0;
}

.section-header p {
  font-size: 18px;
  color: #6b7280;
  margin: 0;
}

.features {
  padding: 80px 20px;
  background-color: white;
  max-width: 1400px;
  margin: 0 auto;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 32px;
}

@media (max-width: 1024px) {
  .features-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .features-grid {
    grid-template-columns: 1fr;
  }
}

.feature-card {
  padding: 32px;
  background-color: #f9fafb;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  transition: all 0.3s;
}

.feature-card:hover {
  border-color: #3b82f6;
  background-color: #eff6ff;
  transform: translateY(-8px);
}

.feature-icon {
  font-size: 40px;
  margin-bottom: 16px;
}

.feature-card h3 {
  font-size: 18px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 12px 0;
}

.feature-card p {
  font-size: 14px;
  color: #6b7280;
  line-height: 1.6;
  margin: 0 0 16px 0;
}

.feature-benefits {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.feature-benefits span {
  font-size: 13px;
  color: #10b981;
  font-weight: 600;
}

/* BENEFITS SECTION */
.benefits {
  padding: 80px 20px;
  background: linear-gradient(135deg, #e0e7ff 0%, #dbeafe 100%);
  max-width: 1400px;
  margin: 0 auto;
}

.benefits-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  align-items: center;
}

@media (max-width: 1024px) {
  .benefits-content {
    grid-template-columns: 1fr;
  }
}

.benefits-text h2 {
  font-size: 40px;
  font-weight: 800;
  color: #1f2937;
  margin: 0 0 32px 0;
}

.benefits-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.benefits-list li {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.benefits-list strong {
  font-size: 16px;
  color: #1f2937;
}

.benefits-list p {
  margin: 0;
  font-size: 14px;
  color: #6b7280;
  line-height: 1.6;
}

.benefits-stats {
  display: grid;
  grid-template-columns: 1fr;
  gap: 20px;
}

.stat-box {
  background-color: white;
  padding: 24px;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.stat-number {
  display: block;
  font-size: 36px;
  font-weight: 800;
  color: #3b82f6;
  margin-bottom: 8px;
}

.stat-desc {
  display: block;
  font-size: 14px;
  color: #6b7280;
}

/* CTA SECTION */
.cta {
  padding: 80px 20px;
  background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8f 100%);
  max-width: 1400px;
  margin: 0 auto;
  text-align: center;
}

.cta-content h2 {
  font-size: 40px;
  font-weight: 800;
  color: white;
  margin: 0 0 12px 0;
}

.cta-content p {
  font-size: 18px;
  color: rgba(255, 255, 255, 0.9);
  margin: 0 0 32px 0;
}

.cta-buttons {
  display: flex;
  gap: 16px;
  justify-content: center;
  flex-wrap: wrap;
}

/* FOOTER */
.footer {
  background-color: #0f172a;
  color: white;
  padding: 60px 20px 20px;
}

.footer-content {
  max-width: 1400px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 40px;
  margin-bottom: 40px;
}

@media (max-width: 768px) {
  .footer-content {
    grid-template-columns: 1fr;
  }
}

.footer-section h4 {
  font-size: 16px;
  font-weight: 700;
  margin: 0 0 16px 0;
}

.footer-section p {
  font-size: 14px;
  color: rgba(255, 255, 255, 0.7);
  line-height: 1.6;
  margin: 0;
}

.footer-section ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-section li {
  margin-bottom: 12px;
}

.footer-section a {
  color: rgba(255, 255, 255, 0.7);
  text-decoration: none;
  font-size: 14px;
  transition: color 0.3s;
}

.footer-section a:hover {
  color: white;
}

.footer-bottom {
  max-width: 1400px;
  margin: 0 auto;
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  text-align: center;
  font-size: 13px;
  color: rgba(255, 255, 255, 0.5);
}
</style>
