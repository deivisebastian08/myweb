// ============================================================================
// SISTEMA EDITORIAL PROFESIONAL - JavaScript
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    
    // Estado de la aplicación
    const appState = {
        currentView: 'list', // 'list' o 'article'
        currentArticleId: null,
        articles: [],
        filteredArticles: []
    };
    
    // ========================================================================
    // NAVEGACIÓN ENTRE VISTAS
    // ========================================================================
    
    function showArticleView(articleId) {
        const article = appState.articles.find(a => a.id == articleId);
        if (!article) return;
        
        appState.currentView = 'article';
        appState.currentArticleId = articleId;
        
        // Actualizar URL simulada
        window.location.hash = `/noticia/${articleId}`;
        
        // Fade out lista
        const listView = document.querySelector('.news-list-view');
        const articleView = document.querySelector('.article-view');
        
        listView.style.opacity = '0';
        setTimeout(() => {
            listView.style.display = 'none';
            articleView.style.display = 'block';
            articleView.classList.add('active');
            
            // Render article
            renderArticle(article);
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
            // Fade in article
            setTimeout(() => {
                articleView.style.opacity = '1';
            }, 50);
        }, 300);
    }
    
    function showListView() {
        appState.currentView = 'list';
        appState.currentArticleId = null;
        
        // Actualizar URL
        window.location.hash = '/noticias';
        
        // Fade out article
        const listView = document.querySelector('.news-list-view');
        const articleView = document.querySelector('.article-view');
        
        articleView.style.opacity = '0';
        setTimeout(() => {
            articleView.style.display = 'none';
            articleView.classList.remove('active');
            listView.style.display = 'block';
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
            // Fade in list
            setTimeout(() => {
                listView.style.opacity = '1';
            }, 50);
        }, 300);
    }
    
    // ========================================================================
    // RENDER DE ARTÍCULO COMPLETO
    // ========================================================================
    
    function renderArticle(article) {
        const articleView = document.querySelector('.article-view');
        if (!articleView) return;
        
        // Calcular tiempo de lectura (aprox 200 palabras/min)
        const words = article.contenido.split(' ').length;
        const readTime = Math.ceil(words / 200);
        
        const html = `
            <div class="reading-progress"></div>
            
            <div class="article-hero">
                <img src="${article.imagen_destacada || 'https://picsum.photos/1200/600'}" 
                     alt="${article.titulo}" 
                     class="article-hero-image">
                <div class="article-hero-overlay"></div>
                <div class="article-hero-content">
                    <div class="article-breadcrumb">
                        <a href="#" onclick="showListView(); return false;">
                            <i class="fas fa-arrow-left"></i> Noticias
                        </a>
                        <span>/</span>
                        <span>${article.titulo}</span>
                    </div>
                    <h1 class="article-title">${article.titulo}</h1>
                    <div class="article-meta">
                        <div class="article-author">
                            <div class="article-author-avatar">
                                ${article.autor_nombre ? article.autor_nombre.charAt(0).toUpperCase() : 'A'}
                            </div>
                            <div class="article-author-info">
                                <div class="article-author-name">${article.autor_nombre || 'Autor'}</div>
                                <div class="article-meta-item">
                                    <i class="far fa-calendar"></i>
                                    ${formatDate(article.fecha_publicacion || article.created_at)}
                                </div>
                            </div>
                        </div>
                        <div class="article-meta-item">
                            <i class="far fa-clock"></i>
                            ${readTime} min lectura
                        </div>
                        <div class="article-meta-item">
                            <i class="far fa-eye"></i>
                            ${article.vistas || 0} vistas
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="article-body">
                <div class="article-content">
                    ${article.contenido}
                </div>
                
                <div class="article-footer">
                    ${article.tags ? `
                        <div class="article-tags">
                            ${article.tags.split(',').map(tag => 
                                `<a href="#" class="article-tag">#${tag.trim()}</a>`
                            ).join('')}
                        </div>
                    ` : ''}
                    
                    <a href="#" onclick="showListView(); return false;" class="btn-back">
                        <i class="fas fa-arrow-left"></i>
                        Volver a noticias
                    </a>
                    
                    <div class="related-articles">
                        <h3>Artículos relacionados</h3>
                        <div class="related-grid" id="relatedArticles"></div>
                    </div>
                </div>
            </div>
        `;
        
        articleView.innerHTML = html;
        
        // Cargar artículos relacionados
        loadRelatedArticles(article);
        
        // Activar progress bar de lectura
        initReadingProgress();
        
        // Parallax en hero image
        initHeroParallax();
    }
    
    // ========================================================================
    // PROGRESS BAR DE LECTURA
    // ========================================================================
    
    function initReadingProgress() {
        const progressBar = document.querySelector('.reading-progress');
        if (!progressBar) return;
        
        window.addEventListener('scroll', () => {
            const windowHeight = window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight - windowHeight;
            const scrolled = window.scrollY;
            const progress = (scrolled / documentHeight) * 100;
            
            progressBar.style.width = `${progress}%`;
        });
    }
    
    // ========================================================================
    // PARALLAX EN HERO
    // ========================================================================
    
    function initHeroParallax() {
        const heroImage = document.querySelector('.article-hero-image');
        if (!heroImage) return;
        
        window.addEventListener('scroll', () => {
            const scrolled = window.scrollY;
            const rate = scrolled * 0.3;
            heroImage.style.transform = `translateY(${rate}px)`;
        });
    }
    
    // ========================================================================
    // ARTÍCULOS RELACIONADOS
    // ========================================================================
    
    function loadRelatedArticles(currentArticle) {
        const container = document.getElementById('relatedArticles');
        if (!container) return;
        
        // Obtener 3 artículos aleatorios diferentes al actual
        const related = appState.articles
            .filter(a => a.id !== currentArticle.id && a.estado === 'publicado')
            .sort(() => 0.5 - Math.random())
            .slice(0, 3);
        
        container.innerHTML = related.map(article => `
            <div class="news-card" onclick="showArticleView(${article.id})">
                <div class="news-card-image">
                    <img src="${article.imagen_destacada || 'https://picsum.photos/400/300'}" 
                         alt="${article.titulo}" loading="lazy">
                </div>
                <div class="news-card-content">
                    <h3 class="news-card-title">${article.titulo}</h3>
                    <div class="news-card-meta">
                        <span class="news-card-date">
                            <i class="far fa-calendar"></i>
                            ${formatDate(article.fecha_publicacion || article.created_at)}
                        </span>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    // ========================================================================
    // BÚSQUEDA EN TIEMPO REAL
    // ========================================================================
    
    const searchInput = document.getElementById('newsSearch');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function(e) {
            const query = e.target.value.toLowerCase().trim();
            filterAndRenderArticles(query);
        }, 300));
    }
    
    // ========================================================================
    // FILTROS
    // ========================================================================
    
    const filterCategory = document.getElementById('filterCategory');
    const filterStatus = document.getElementById('filterStatus');
    const filterSort = document.getElementById('filterSort');
    
    if (filterCategory) {
        filterCategory.addEventListener('change', () => filterAndRenderArticles());
    }
    
    if (filterStatus) {
        filterStatus.addEventListener('change', () => filterAndRenderArticles());
    }
    
    if (filterSort) {
        filterSort.addEventListener('change', () => filterAndRenderArticles());
    }
    
    function filterAndRenderArticles(searchQuery = '') {
        const categoryFilter = filterCategory ? filterCategory.value : '';
        const statusFilter = filterStatus ? filterStatus.value : '';
        const sortBy = filterSort ? filterSort.value : 'recent';
        
        let filtered = [...appState.articles];
        
        // Búsqueda por texto
        if (searchQuery) {
            filtered = filtered.filter(article => 
                article.titulo.toLowerCase().includes(searchQuery) ||
                (article.extracto && article.extracto.toLowerCase().includes(searchQuery)) ||
                (article.contenido && article.contenido.toLowerCase().includes(searchQuery))
            );
        }
        
        // Filtro por categoría
        if (categoryFilter) {
            filtered = filtered.filter(article => 
                article.categoria_id == categoryFilter
            );
        }
        
        // Filtro por estado
        if (statusFilter) {
            filtered = filtered.filter(article => 
                article.estado === statusFilter
            );
        }
        
        // Ordenamiento
        switch(sortBy) {
            case 'recent':
                filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                break;
            case 'views':
                filtered.sort((a, b) => (b.vistas || 0) - (a.vistas || 0));
                break;
            case 'az':
                filtered.sort((a, b) => a.titulo.localeCompare(b.titulo));
                break;
        }
        
        appState.filteredArticles = filtered;
        renderArticleCards(filtered);
    }
    
    function renderArticleCards(articles) {
        const grid = document.querySelector('.news-grid');
        if (!grid) return;
        
        if (articles.length === 0) {
            grid.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 3rem;">
                    <i class="fas fa-search" style="font-size: 3rem; color: var(--color-text-light); margin-bottom: 1rem;"></i>
                    <p style="font-size: 1.2rem; color: var(--color-text-light);">No se encontraron noticias</p>
                </div>
            `;
            return;
        }
        
        grid.innerHTML = articles.map((article, index) => `
            <div class="news-card" style="animation-delay: ${index * 0.1}s">
                <div class="news-card-image" onclick="showArticleView(${article.id})">
                    <img src="${article.imagen_destacada || 'https://picsum.photos/400/300?random=' + article.id}" 
                         alt="${article.titulo}" loading="lazy">
                    <span class="news-card-status status-${article.estado}">
                        ${article.estado}
                    </span>
                </div>
                <div class="news-card-content" onclick="showArticleView(${article.id})">
                    ${article.categoria_nombre ? `<span class="news-card-category">${article.categoria_nombre}</span>` : ''}
                    <h3 class="news-card-title">${article.titulo}</h3>
                    <p class="news-card-excerpt">${article.extracto || 'Sin extracto disponible'}</p>
                    <div class="news-card-meta">
                        <div class="news-card-author">
                            <div class="news-card-author-avatar">
                                ${article.autor_nombre ? article.autor_nombre.charAt(0).toUpperCase() : 'A'}
                            </div>
                            <span>${article.autor_nombre || 'Autor'}</span>
                        </div>
                        <span class="news-card-date">
                            <i class="far fa-calendar"></i>
                            ${formatDate(article.fecha_publicacion || article.created_at)}
                        </span>
                    </div>
                </div>
                <div class="news-card-actions">
                    <button class="btn-edit" onclick="event.stopPropagation(); editArticle(${article.id})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-view" onclick="event.stopPropagation(); showArticleView(${article.id})" title="Ver">
                        <i class="fas fa-eye"></i>
                    </button>
                    ${article.puede_eliminar ? `
                        <button class="btn-delete" onclick="event.stopPropagation(); deleteArticle(${article.id})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    ` : ''}
                </div>
            </div>
        `).join('');
    }
    
    // ========================================================================
    // UTILIDADES
    // ========================================================================
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffTime = Math.abs(now - date);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays === 0) return 'Hoy';
        if (diffDays === 1) return 'Ayer';
        if (diffDays < 7) return `Hace ${diffDays} días`;
        
        return date.toLocaleDateString('es-ES', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    }
    
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // ========================================================================
    // MODAL CREAR/EDITAR
    // ========================================================================
    
    const createBtn = document.getElementById('createNewsBtn');
    const modal = document.getElementById('newsModal');
    const closeModal = document.querySelector('.news-modal-close');
    
    if (createBtn) {
        createBtn.addEventListener('click', () => {
            openModal();
        });
    }
    
    if (closeModal) {
        closeModal.addEventListener('click', () => {
            closeNewsModal();
        });
    }
    
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeNewsModal();
            }
        });
    }
    
    function openModal(articleId = null) {
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            if (articleId) {
                // Cargar datos del artículo para editar
                const article = appState.articles.find(a => a.id == articleId);
                if (article) {
                    document.getElementById('newsId').value = article.id;
                    document.getElementById('newsTitle').value = article.titulo;
                    document.getElementById('newsExcerpt').value = article.extracto || '';
                    document.getElementById('newsContent').value = article.contenido || '';
                    document.getElementById('newsImage').value = article.imagen_destacada || '';
                    document.getElementById('newsCategory').value = article.categoria_id || '';
                    document.getElementById('newsStatus').value = article.estado;
                    document.getElementById('newsTags').value = article.tags || '';
                }
            }
        }
    }
    
    function closeNewsModal() {
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
            // Reset form
            document.getElementById('newsForm').reset();
        }
    }
    
    // Contador de caracteres
    const titleInput = document.getElementById('newsTitle');
    const titleCounter = document.getElementById('titleCounter');
    
    if (titleInput && titleCounter) {
        titleInput.addEventListener('input', (e) => {
            const length = e.target.value.length;
            titleCounter.textContent = `${length}/80 caracteres`;
            if (length > 80) {
                titleCounter.style.color = 'var(--color-danger)';
            } else {
                titleCounter.style.color = 'var(--color-text-light)';
            }
        });
    }
    
    const excerptInput = document.getElementById('newsExcerpt');
    const excerptCounter = document.getElementById('excerptCounter');
    
    if (excerptInput && excerptCounter) {
        excerptInput.addEventListener('input', (e) => {
            const length = e.target.value.length;
            excerptCounter.textContent = `${length}/160 caracteres`;
            if (length > 160) {
                excerptCounter.style.color = 'var(--color-danger)';
            } else {
                excerptCounter.style.color = 'var(--color-text-light)';
            }
        });
    }
    
    // ========================================================================
    // FUNCIONES GLOBALES (para onclick en HTML)
    // ========================================================================
    
    window.showArticleView = showArticleView;
    window.showListView = showListView;
    window.editArticle = openModal;
    window.closeNewsModal = closeNewsModal;
    
    window.deleteArticle = function(articleId) {
        if (confirm('¿Estás seguro de eliminar esta noticia?')) {
            // Enviar formulario de eliminación
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="id" value="${articleId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    };
    
    // ========================================================================
    // INICIALIZACIÓN
    // ========================================================================
    
    // Cargar artículos del DOM
    const articlesData = document.getElementById('articlesData');
    if (articlesData) {
        try {
            appState.articles = JSON.parse(articlesData.textContent);
            appState.filteredArticles = [...appState.articles];
        } catch(e) {
            console.error('Error parsing articles data:', e);
        }
    }
    
    // Manejar URL hash inicial
    const hash = window.location.hash;
    if (hash && hash.startsWith('#/noticia/')) {
        const articleId = hash.replace('#/noticia/', '');
        showArticleView(articleId);
    }
    
    console.log('📰 Sistema Editorial cargado correctamente');
});
