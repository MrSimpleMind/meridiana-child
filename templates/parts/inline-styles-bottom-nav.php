/* Bottom Navigation Mobile - Menu Overlay Styles */

.bottom-nav-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 999;
    display: flex;
    flex-direction: column;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        transform: translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.bottom-nav-overlay[hidden] {
    display: none !important;
}

.bottom-nav-overlay__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background-color: #FFFFFF;
    border-bottom: 1px solid #E5E7EB;
}

.bottom-nav-overlay__header h2 {
    font-size: 18px;
    font-weight: 600;
    color: #1F2937;
    margin: 0;
}

.bottom-nav-overlay__close {
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bottom-nav-overlay__close svg {
    width: 24px;
    height: 24px;
    color: #6B7280;
}

.bottom-nav-overlay__menu {
    flex: 1;
    overflow-y: auto;
    background-color: #FFFFFF;
    display: flex;
    flex-direction: column;
}

.bottom-nav-overlay__item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    color: #1F2937;
    text-decoration: none;
    border-bottom: 1px solid #F3F4F6;
    transition: background-color 0.2s ease;
    font-size: 16px;
    font-weight: 500;
}

.bottom-nav-overlay__item i,
.bottom-nav-overlay__item svg {
    width: 20px;
    height: 20px;
    color: #6B7280;
    flex-shrink: 0;
}

.bottom-nav-overlay__item:active {
    background-color: #F9FAFB;
}

.bottom-nav-overlay__item.active {
    color: #ab1120;
    background-color: #fef2f3;
}

.bottom-nav-overlay__item.active i,
.bottom-nav-overlay__item.active svg {
    color: #ab1120;
}
