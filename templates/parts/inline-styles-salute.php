<style>
/* PROMPT 8 - TEMPORARY INLINE STYLES */
/* Single Salute e Benessere - Grid Layout + Responsive */

.single-salute-benessere-page {
    background-color: #FFFFFF;
    min-height: 100vh;
}

.single-salute-benessere__layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 48px;
    margin-top: 32px;
}

@media (min-width: 768px) {
    .single-salute-benessere__layout {
        grid-template-columns: 1fr;
        gap: 48px;
    }
}

@media (min-width: 1200px) {
    .single-salute-benessere__layout {
        grid-template-columns: 1fr;
        gap: 48px;
    }
}

/* Featured Image */
.single-salute-benessere__featured-image {
    aspect-ratio: 16 / 9;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    margin: 32px 0;
}

@media (max-width: 768px) {
    .single-salute-benessere__featured-image {
        aspect-ratio: 4 / 3;
        margin: 24px 0;
    }
}

.single-salute-benessere__image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

/* Header */
.single-salute-benessere__header {
    margin-bottom: 32px;
}

.single-salute-benessere__title {
    font-size: 30px;
    font-weight: 700;
    line-height: 1.25;
    color: #1F2937;
    margin: 0;
}

@media (min-width: 768px) {
    .single-salute-benessere__title {
        font-size: 36px;
    }
}

/* Content */
.single-salute-benessere__content {
    grid-column: 1;
}

.single-salute-benessere__body {
    font-size: 16px;
    line-height: 1.5;
    color: #1F2937;
}

.single-salute-benessere__body p {
    margin-bottom: 20px;
}

.single-salute-benessere__body a {
    color: #ab1120;
    text-decoration: underline;
    transition: color 0.2s ease;
}

.single-salute-benessere__body a:hover {
    color: #8a0e1a;
}

/* Sidebar - ora stacked under content */
.single-salute-benessere__sidebar {
    grid-column: 1;
    margin-top: 32px;
}

/* Section */
.single-salute-benessere__section {
    background-color: #F8F9FA;
    border-radius: 8px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.single-salute-benessere__section-title {
    font-size: 18px;
    font-weight: 600;
    color: #1F2937;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 20px;
    margin: 0 0 20px 0;
}

.single-salute-benessere__section-title i,
.single-salute-benessere__section-title svg {
    width: 20px;
    height: 20px;
    color: #ab1120;
    flex-shrink: 0;
}

/* Risorse List */
.single-salute-benessere__risorse-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.single-salute-benessere__risorsa-item {
    list-style: none;
}

.single-salute-benessere__risorsa-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background-color: #FFFFFF;
    border: 1px solid #E5E7EB;
    border-radius: 6px;
    color: #ab1120;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.single-salute-benessere__risorsa-link i,
.single-salute-benessere__risorsa-link svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

.single-salute-benessere__risorsa-link span {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.single-salute-benessere__risorsa-link small {
    font-size: 12px;
    color: #6B7280;
    flex-shrink: 0;
}

.single-salute-benessere__risorsa-link:hover {
    background-color: #fef2f3;
    border-color: #ab1120;
    color: #8a0e1a;
    transform: translateX(2px);
}

.single-salute-benessere__risorsa-link:active {
    transform: translateX(0);
}

.single-salute-benessere__risorsa-link:focus-visible {
    outline: none;
    box-shadow: 0 0 0 3px rgba(171, 17, 32, 0.1);
}

/* Back link styling */
.back-link-wrapper {
    margin-bottom: 24px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #ab1120;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: color 0.2s ease;
}

.back-link i,
.back-link svg {
    width: 16px;
    height: 16px;
}

.back-link:hover {
    color: #8a0e1a;
}

/* Breadcrumb styling */
.meridiana-breadcrumb {
    margin-bottom: 32px;
}
</style>
