#!/usr/bin/env python3
"""Dollar-Cost Averaging diagram for kids – clean version using scatter for balls."""
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import matplotlib.patches as mpatches
from matplotlib.patches import FancyBboxPatch
import matplotlib.font_manager as fm
import numpy as np
from scipy.interpolate import CubicSpline

# ---------- Font ----------
JP = '/usr/share/fonts/opentype/ipafont-gothic/ipag.ttf'

def jf(size=12, bold=False):
    fp = fm.FontProperties(fname=JP)
    fp.set_size(size)
    if bold:
        fp.set_weight('bold')
    return fp

# ---------- Data ----------
months = ['1月', '2月', '3月', '4月', '5月']
mpos   = [1, 2, 3, 4, 5]

prices_A = [100, 200, 100,  50, 100]   # dips low in April
balls_A  = [200 // p for p in prices_A]   # [2,1,2,4,2] = 11

prices_B = [100, 200, 100, 200, 100]   # stays high
balls_B  = [200 // p for p in prices_B]   # [2,1,2,1,2] = 8

# ---------- Style ----------
BALL_C  = '#F07030'
BALL_E  = '#B84808'
SHINE_C = '#FFB878'
CURVE_A = '#1A4A8A'
BG_MAIN = '#EEF5FF'
BG_FIG  = '#FFFFFF'
GRID_C  = '#6699CC'

# ---------- Figure ----------
fig = plt.figure(figsize=(13, 19), facecolor=BG_FIG)

# Axes layout: title + 2 charts + comparison strip
ax_head = fig.add_axes([0.0,  0.915, 1.0,  0.085])
ax_A    = fig.add_axes([0.09, 0.515, 0.83, 0.38])
ax_B    = fig.add_axes([0.09, 0.115, 0.83, 0.38])
ax_cmp  = fig.add_axes([0.05, 0.01,  0.90, 0.09])

# ================================================================
def draw_chart(ax, prices, balls, title_txt, title_clr,
               highlight_x=None):
    """
    Draw one scenario chart.
    highlight_x : x-position (month index) to draw a glow ring (e.g. 4 for April).
    """
    ax.set_facecolor(BG_MAIN)
    YMIN, YMAX = -15, 265
    ax.set_ylim(YMIN, YMAX)
    ax.set_xlim(0.3, 5.7)

    # --- grid ---
    for y in [50, 100, 200]:
        ax.axhline(y, color=GRID_C, lw=1.1, ls='--', alpha=0.55, zorder=1)
    for x in mpos:
        ax.axvline(x, color=GRID_C, lw=0.9, ls='--', alpha=0.40, zorder=1)

    # --- 100円 reference solid line ---
    ax.axhline(100, color=CURVE_A, lw=1.6, ls='-', alpha=0.25, zorder=2)

    # --- smooth price curve ---
    cs = CubicSpline(mpos, prices)
    xs = np.linspace(1, 5, 400)
    ax.plot(xs, cs(xs), '-', color=CURVE_A, lw=3.2, zorder=4,
            solid_capstyle='round')

    # --- balls ---
    # We need scatter size.  At 150 dpi, figsize 13×19:
    #   ax_A height ≈ 0.38×19×150 = 1083 px  →  yrange 280 → 3.87 px/unit
    #   ax_A width  ≈ 0.83×13×150 = 1618 px  →  xrange 5.4 → 299 px/unit
    # s=500  →  diam ≈ 2√(500/π) pts = 25.2 pts × 2.08 px/pt ≈ 52 px
    # y spacing to avoid overlap: need > 52 px / 3.87 ≈ 13.5 data-units → use 20
    BALL_S   = 500    # scatter marker area (points^2)
    SHINE_S  = 80
    Y_GAP    = 20     # data-unit spacing for stacked balls
    X_GAP    = 0.22   # data-unit x-offset for 2x2 grid

    for mx, price, n in zip(mpos, prices, balls):

        # glow ring for highlighted month
        if highlight_x and mx == highlight_x:
            ax.scatter([mx], [price],
                       s=BALL_S * 6, c='#FFEE44', edgecolors='#FFAA00',
                       linewidths=0, alpha=0.35, zorder=3)

        # ball positions
        if n == 1:
            bx = [mx];           by = [price]
        elif n == 2:
            bx = [mx,  mx]
            by = [price - Y_GAP/2, price + Y_GAP/2]
        else:  # n == 4  →  2×2 grid
            bx = [mx - X_GAP, mx + X_GAP, mx - X_GAP, mx + X_GAP]
            by = [price + Y_GAP/2, price + Y_GAP/2,
                  price - Y_GAP/2, price - Y_GAP/2]

        # main ball
        ax.scatter(bx, by, s=BALL_S, c=BALL_C, edgecolors=BALL_E,
                   linewidths=2.2, zorder=6, marker='o')
        # shine highlight (upper-left of each ball)
        shine_x = [x - 0.04 for x in bx]
        shine_y = [y + 4     for y in by]
        ax.scatter(shine_x, shine_y, s=SHINE_S, c=SHINE_C,
                   edgecolors='none', zorder=7, marker='o', alpha=0.80)

        # count badge above top ball
        top_y = max(by)
        badge_y = top_y + 14
        ax.text(mx, badge_y,
                f'{n}個', ha='center', va='bottom',
                fontproperties=jf(12, bold=True),
                color='#AA3300', zorder=8)

    # --- Y-axis labels ---
    ax.set_yticks([])
    for y_val, lbl in [(50, '50円'), (100, '100円'), (200, '200円')]:
        ax.text(-0.015, y_val, lbl,
                transform=ax.get_yaxis_transform(),
                ha='right', va='center',
                fontproperties=jf(13, bold=True),
                color='#1A4A8A')

    # --- X-axis month labels ---
    ax.set_xticks([])
    for mx, m in zip(mpos, months):
        ax.text(mx, YMIN - 5, m,
                ha='center', va='top',
                fontproperties=jf(14, bold=True), color='#1A4A8A')

    # --- budget label (right side) ---
    ax.text(5.72, 100, '毎月\n200円\n購入',
            ha='left', va='center',
            fontproperties=jf(10), color='#1A4A8A')

    # --- scenario title ---
    ax.text(0.5, YMAX - 5, title_txt,
            ha='left', va='top',
            fontproperties=jf(15, bold=True), color=title_clr, zorder=9)

    # spines
    for sp in ax.spines.values():
        sp.set_visible(False)
    ax.tick_params(left=False, bottom=False)


# ================================================================
# Header / Title
ax_head.set_axis_off()
ax_head.set_facecolor(BG_FIG)
ax_head.text(0.5, 0.78, 'ドルコスト平均法ってなに？',
             ha='center', va='center',
             fontproperties=jf(30, bold=True), color='#1A3A6A',
             transform=ax_head.transAxes)
ax_head.text(0.5, 0.22,
             '毎月おなじ金額（200円）でボールを買い続けると、安い時期があると得をする！',
             ha='center', va='center',
             fontproperties=jf(15), color='#445599',
             transform=ax_head.transAxes)

# Legend strip between header and charts
ax_leg = fig.add_axes([0.09, 0.895, 0.83, 0.022])
ax_leg.set_axis_off()
# mini ball
ax_leg.scatter([0.022], [0.5], s=200, c=BALL_C, edgecolors=BALL_E,
               linewidths=1.8, zorder=5,
               transform=ax_leg.transAxes)
ax_leg.text(0.045, 0.5,
            '= ボール 1個（100円）　　毎月200円分のボールを買います',
            ha='left', va='center',
            fontproperties=jf(13), color='#333355',
            transform=ax_leg.transAxes)

# --- Draw both scenarios ---
draw_chart(ax_A, prices_A, balls_A,
           '【ケース①】 価格が下がる時があった場合',
           '#1A4A8A', highlight_x=4)

draw_chart(ax_B, prices_B, balls_B,
           '【ケース②】 価格が下がらなかった場合',
           '#1A4A8A', highlight_x=None)

# Divider between the two charts
ax_div = fig.add_axes([0.04, 0.5, 0.92, 0.003])
ax_div.set_axis_off()
ax_div.axhline(0.5, color='#AABBDD', lw=2)

# ================================================================
# Comparison strip at bottom
ax_cmp.set_axis_off()
ax_cmp.set_facecolor('#FFF8E0')
# outer rounded rect
outer = FancyBboxPatch((0.01, 0.04), 0.98, 0.92,
                        boxstyle="round,pad=0.015",
                        facecolor='#FFF8E0', edgecolor='#DDBB55',
                        linewidth=2.5, transform=ax_cmp.transAxes, zorder=1)
ax_cmp.add_patch(outer)

# Box A (winner)
boxA = FancyBboxPatch((0.03, 0.1), 0.40, 0.80,
                       boxstyle="round,pad=0.015",
                       facecolor='#E0F0FF', edgecolor='#2255AA',
                       linewidth=2.5, transform=ax_cmp.transAxes, zorder=2)
ax_cmp.add_patch(boxA)

# Box B
boxB = FancyBboxPatch((0.56, 0.1), 0.40, 0.80,
                       boxstyle="round,pad=0.015",
                       facecolor='#F4F4F4', edgecolor='#999999',
                       linewidth=1.8, transform=ax_cmp.transAxes, zorder=2)
ax_cmp.add_patch(boxB)

ax_cmp.text(0.23, 0.80, 'ケース①  ボールの合計',
            ha='center', va='top',
            fontproperties=jf(13, bold=True), color='#1A4A8A',
            transform=ax_cmp.transAxes, zorder=3)
ax_cmp.text(0.23, 0.32, f'{sum(balls_A)} 個  ★ お得！',
            ha='center', va='center',
            fontproperties=jf(22, bold=True), color='#CC2200',
            transform=ax_cmp.transAxes, zorder=3)

ax_cmp.text(0.50, 0.50, '>',
            ha='center', va='center',
            fontproperties=jf(30, bold=True), color='#CC2200',
            transform=ax_cmp.transAxes, zorder=3)

ax_cmp.text(0.76, 0.80, 'ケース②  ボールの合計',
            ha='center', va='top',
            fontproperties=jf(13, bold=True), color='#555555',
            transform=ax_cmp.transAxes, zorder=3)
ax_cmp.text(0.76, 0.32, f'{sum(balls_B)} 個',
            ha='center', va='center',
            fontproperties=jf(22, bold=True), color='#777777',
            transform=ax_cmp.transAxes, zorder=3)

# Save
OUT = '/home/user/my-first-app/dca_diagram.png'
plt.savefig(OUT, dpi=150, bbox_inches='tight',
            facecolor=BG_FIG, edgecolor='none')
print(f"Saved → {OUT}")
