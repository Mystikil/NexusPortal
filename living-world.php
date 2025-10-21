<?php
$title = 'Living World Atlas';
require __DIR__ . '/partials/header.php';
?>
<!-- layout:content:start -->
<article class="living-world">
    <section class="living-hero">
        <div class="hero-copy">
            <p class="eyebrow">Living World Protocol</p>
            <h1>The Atlas That Never Sleeps</h1>
            <p class="lead">Stay ahead of every seasonal shift, faction coup, and convergence threat. The Living World Protocol connects every player action to the fate of Nexus One.</p>
            <div class="hero-stats">
                <div>
                    <span class="label">Cycle Length</span>
                    <span class="value">21 days</span>
                </div>
                <div>
                    <span class="label">Active Phases</span>
                    <span class="value">Dawnrise &rsaquo; Zenith &rsaquo; Duskwane</span>
                </div>
                <div>
                    <span class="label">Anchor Sites</span>
                    <span class="value">12 rotating stories</span>
                </div>
            </div>
        </div>
        <div class="hero-card">
            <h2>Tonight&apos;s Forecast</h2>
            <ul>
                <li><strong>Server Review:</strong> Mondays advance the cycle if the phase is 48h old.</li>
                <li><strong>Override Triggers:</strong> Realm votes &amp; boss kills can fast-forward seasons instantly.</li>
                <li><strong>Next Update:</strong> Convergence forecast hits Sundays @ 12:00 server.</li>
            </ul>
            <a class="cta" href="#living-checklist">Prep the next moves</a>
        </div>
    </section>

    <section class="living-carousel" aria-label="Cycle showcase" data-carousel>
        <div class="carousel-viewport">
            <article class="carousel-slide is-active" data-slide="0">
                <figure style="background-image: url('/N1/assets/img/living-dawnrise.svg');">
                    <figcaption>
                        <h2>Dawnrise Surge</h2>
                        <p>Crafting labs flare to life as flora explodes across Nexus One. Track respawning nodes and stock rare reagents before Zenith crowds the markets.</p>
                    </figcaption>
                </figure>
            </article>
            <article class="carousel-slide" data-slide="1">
                <figure style="background-image: url('/N1/assets/img/living-zenith.svg');">
                    <figcaption>
                        <h2>Zenith Pressure</h2>
                        <p>Faction contracts spill over with gold bonuses while shard rifts overflow with monsters. Rally your crew for high-yield hunts.</p>
                    </figcaption>
                </figure>
            </article>
            <article class="carousel-slide" data-slide="2">
                <figure style="background-image: url('/N1/assets/img/living-duskwane.svg');">
                    <figcaption>
                        <h2>Duskwane Veil</h2>
                        <p>Shadow events ignite every four hours. Night merchants surface while undead spill from spectral fractures—secure settlements before curfews strike.</p>
                    </figcaption>
                </figure>
            </article>
        </div>
        <div class="carousel-controls">
            <button type="button" class="carousel-button prev" aria-label="Previous slide">&#8592;</button>
            <button type="button" class="carousel-button next" aria-label="Next slide">&#8594;</button>
        </div>
        <div class="carousel-indicators" role="tablist" aria-label="Carousel indicators">
            <button type="button" aria-label="View Dawnrise" aria-controls="" data-slide-to="0" class="active"></button>
            <button type="button" aria-label="View Zenith" aria-controls="" data-slide-to="1"></button>
            <button type="button" aria-label="View Duskwane" aria-controls="" data-slide-to="2"></button>
        </div>
    </section>

    <section class="living-grid">
        <header>
            <h2>Seasonal Cadence</h2>
            <p>Monitor the core rhythms of the world to orchestrate your next campaign.</p>
        </header>
        <div class="grid">
            <article>
                <h3>Rotation Rules</h3>
                <ul>
                    <li>Weekly maintenance on Mondays can push the season forward if the phase is stale.</li>
                    <li>World votes and raid boss defeats may trigger instant overrides.</li>
                    <li>Keep atlas alerts enabled to track cycle boundaries in real time.</li>
                </ul>
            </article>
            <article>
                <h3>Phase Breakdown</h3>
                <table aria-label="Seasonal effects">
                    <thead>
                        <tr><th>Phase</th><th>World</th><th>City</th><th>Wilderness</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Dawnrise</td><td>Boosted crafting yields</td><td>Rare reagents flood markets</td><td>Foraging nodes respawn 2&times;</td></tr>
                        <tr><td>Zenith</td><td>+15% faction contract gold</td><td>Guards tighten patrols</td><td>Shard rifts surge with monsters</td></tr>
                        <tr><td>Duskwane</td><td>Shadow events every 4h</td><td>Night merchants appear</td><td>Undead replace standard spawns</td></tr>
                    </tbody>
                </table>
            </article>
        </div>
    </section>

    <section class="faction-dominance">
        <header>
            <h2>Dynamic Faction Web</h2>
            <p>Three forces vie for territory. Every contract or lieutenant kill shifts the balance.</p>
        </header>
        <div class="faction-cards">
            <article>
                <h3>Aetherwatch</h3>
                <p>Hold the ley-lines to slash fast travel costs and earn +5% spell critical chance in claimed provinces.</p>
                <p class="threshold">Dominance threshold: 40 points</p>
            </article>
            <article>
                <h3>Crimson Choir</h3>
                <p>Empower weapon imbues for 20% longer runs. Elite bounty hunts spawn as their influence rises.</p>
                <p class="threshold">Neglect for 3 days triggers a counter-offensive.</p>
            </article>
            <article>
                <h3>Verdant Veil</h3>
                <p>Halve mount stamina drain and scoop +1 resources from every gathering node when this faction ascends.</p>
                <p class="threshold">Coordinate conservation tasks to prevent territory flips.</p>
            </article>
        </div>
    </section>

    <section class="settlement-health">
        <header>
            <h2>Settlement Vitality</h2>
            <p>Wellbeing scores control the amenities you rely on between battles.</p>
        </header>
        <div class="health-grid">
            <article>
                <h3>Wellbeing Inputs</h3>
                <ul>
                    <li>Supply caravans and player deliveries bolster stores.</li>
                    <li>Faction control modifies morale and defense ratings.</li>
                    <li>Event completions raise or lower arcane shielding.</li>
                </ul>
            </article>
            <article class="status-tiers">
                <h3>Tier Consequences</h3>
                <ul>
                    <li><strong>80+</strong> &mdash; Festival vendors, global XP buffs.</li>
                    <li><strong>50&ndash;79</strong> &mdash; Standard services stay open.</li>
                    <li><strong>20&ndash;49</strong> &mdash; Curfews, bank lockdowns, +5% taxes.</li>
                    <li><strong>&lt;20</strong> &mdash; Evacuation quests until stability returns.</li>
                </ul>
            </article>
        </div>
    </section>

    <section class="living-bestiary">
        <header>
            <h2>Living Bestiary &amp; Convergence</h2>
            <p>Adaptive spawns ensure every biome reacts to the pressure you place on it.</p>
        </header>
        <div class="bestiary-cards">
            <article>
                <h3>Population Pressure</h3>
                <p>Overhunting moves apex beasts into neighboring regions, changing travel routes and loot tables.</p>
            </article>
            <article>
                <h3>Convergence Events</h3>
                <p>When apex signatures overlap, a rotating world boss forms. Track aurora fractures to catch the timer.</p>
            </article>
            <article>
                <h3>Conservation Tasks</h3>
                <p>Weekly research contracts restore biome balance and grant eco-ally titles plus Atlas reputation.</p>
            </article>
        </div>
    </section>

    <section id="living-checklist" class="player-checklist">
        <header>
            <h2>Player Influence Checklist</h2>
            <p>Follow these five actions to keep the realm thriving through every cycle.</p>
        </header>
        <ol>
            <li>Monitor the `/living-world` atlas and in-game tracker for current seasonal phases.</li>
            <li>Stabilize settlements above 40 wellbeing before Duskwane curfews trigger.</li>
            <li>Run daily faction contracts to cement bonuses ahead of power swings.</li>
            <li>Review anchor site rotations each Sunday to prep for cascading storylines.</li>
            <li>Carry conservation lures when harvesting to prevent convergence catastrophes.</li>
        </ol>
    </section>

    <section class="rewards">
        <header>
            <h2>Rewards &amp; Progression</h2>
            <p>Your influence feeds directly into long-term advancement systems.</p>
        </header>
        <div class="rewards-grid">
            <article>
                <h3>Atlas Reputation</h3>
                <p>Contribute to seasonal objectives to unlock sigils at ranks 5, 10, and 15.</p>
            </article>
            <article>
                <h3>Cycle Vault</h3>
                <p>Claim weekly caches themed after the dominant faction for catalysts and upgrades.</p>
            </article>
            <article>
                <h3>Legacy Echoes</h3>
                <p>Track defended, restored, and conquered provinces across multiple cycles for prestige rewards.</p>
            </article>
        </div>
    </section>

    <section class="living-faq">
        <header>
            <h2>Frequently Asked</h2>
            <p>Key clarifications before your next sortie.</p>
        </header>
        <dl>
            <dt>Can a single guild control the entire world?</dt>
            <dd>No. Control caps per province, demanding cross-faction cooperation to keep bonuses active.</dd>
            <dt>What if every settlement collapses?</dt>
            <dd>Refugees trigger a realm-wide rally that, once completed, restores all settlements to 35 wellbeing.</dd>
            <dt>How are convergence events announced?</dt>
            <dd>Watch for aurora fractures in the skybox or open the `/events` tracker; this page mirrors the same timers.</dd>
            <dt>Does the Living World affect PvP?</dt>
            <dd>Seasonal buffs apply slight modifiers (+2% damage in Zenith, +5% lifesteal during Duskwane nights) while brackets remain skill-matched.</dd>
        </dl>
    </section>

    <section class="timeline">
        <header>
            <h2>Weekly Timeline</h2>
            <p>Sync your schedule with the realm&apos;s standing meetings.</p>
        </header>
        <ul class="timeline-list">
            <li><span class="day">Monday</span><span class="detail">Maintenance review &amp; potential cycle advancement.</span></li>
            <li><span class="day">Wednesday</span><span class="detail">Settlement wellbeing recalculates at 18:00 server.</span></li>
            <li><span class="day">Friday</span><span class="detail">Anchor site storylines rotate at 20:00 server.</span></li>
            <li><span class="day">Sunday</span><span class="detail">Convergence forecast goes live at 12:00 server.</span></li>
        </ul>
    </section>
</article>
<!-- layout:content:end -->
<?php require __DIR__ . '/partials/footer.php'; ?>
