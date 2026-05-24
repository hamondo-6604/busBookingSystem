<style>
    .seat-sheet-bus {
        border: 3px solid #cbd5e1;
        border-radius: 32px;
        padding: 32px 16px;
        background: #f8fafc;
        position: relative;
        max-width: max-content;
        margin: 0 auto;
    }
    .seat-sheet-bus-front {
        position: absolute;
        top: -16px;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 32px;
        background: #cbd5e1;
        border-radius: 40px 40px 0 0;
    }
    .seat-sheet-steering {
        position: absolute;
        top: 16px;
        left: 24px;
        width: 32px;
        height: 32px;
        border: 3px solid #64748b;
        border-radius: 50%;
    }
    .seat-sheet-seat {
        width: 44px;
        height: 52px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
        padding-bottom: 4px;
        border-radius: 8px 8px 6px 6px;
        font-weight: 700;
        font-size: 11px;
        cursor: pointer;
        transition: all 0.15s ease;
        position: relative;
        user-select: none;
        border: 2px solid #e2e8f0;
        background: #fff;
        color: #64748b;
    }
    .seat-sheet-seat::before {
        content: '';
        position: absolute;
        top: 4px;
        width: 28px;
        height: 6px;
        border-radius: 3px;
        background: inherit;
        border: inherit;
        border-bottom: none;
    }
    .seat-sheet-seat.available:hover {
        border-color: #22c55e;
        color: #16a34a;
        transform: translateY(-1px);
    }
    .seat-sheet-seat.selected {
        background: #22c55e;
        border-color: #16a34a;
        color: #fff;
    }
    .seat-sheet-seat.occupied {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #94a3b8;
        cursor: not-allowed;
    }
    .seat-sheet-seat.occupied::after {
        content: '×';
        position: absolute;
        font-size: 20px;
        font-weight: 300;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    .seat-sheet-seat.own-booking {
        background: #bae6fd;
        border-color: #0ea5e9;
        color: #0284c7;
        cursor: not-allowed;
    }
    .seat-sheet-cell-empty { width: 44px; height: 52px; }
    .seat-sheet-cell-aisle { width: 24px; height: 52px; }
    .seat-sheet-cell-door {
        width: 44px; height: 52px;
        border: 2px dashed #94a3b8;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        color: #94a3b8; font-size: 9px; text-transform: uppercase; font-weight: bold;
    }
    .seat-sheet-cell-driver {
        width: 44px; height: 52px;
        background: #475569; border-radius: 10px;
        display: flex; align-items: center; justify-content: center; color: white;
    }
    .seat-sheet-legend-box { width: 20px; height: 20px; border-radius: 4px; }
    .seat-info-tab.active {
        color: #ea580c;
        border-bottom: 2px solid #ea580c;
        font-weight: 600;
    }
    .seat-info-panel { display: none; }
    .seat-info-panel.active { display: block; }
    #seat-sheet-overlay {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity 0.35s ease, visibility 0.35s ease, backdrop-filter 0.35s ease;
        backdrop-filter: blur(0px);
        -webkit-backdrop-filter: blur(0px);
    }
    #seat-sheet-overlay.open {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
    }
    #seat-sheet-panel {
        transform: translateY(100%);
        transition: transform 0.4s cubic-bezier(0.32, 0.72, 0, 1);
    }
    #seat-sheet-overlay.open #seat-sheet-panel {
        transform: translateY(0);
    }
    .radio-circle-board {
        width: 18px;
        height: 18px;
        border: 2px solid #cbd5e1;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    label:has(input[type="radio"]:checked) .radio-circle-board {
        border-color: #ea580c;
        background-color: #ea580c;
    }
    label:has(input[type="radio"]:checked) .radio-circle-board::after {
        content: '';
        width: 6px;
        height: 6px;
        background: white;
        border-radius: 50%;
        display: block;
    }
    
    .radio-circle-drop {
        width: 18px;
        height: 18px;
        border: 2px solid #cbd5e1;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    label:has(input[type="radio"]:checked) .radio-circle-drop {
        border-color: #10b981;
        background-color: #10b981;
    }
    label:has(input[type="radio"]:checked) .radio-circle-drop::after {
        content: '';
        width: 6px;
        height: 6px;
        background: white;
        border-radius: 50%;
        display: block;
    }
</style>
