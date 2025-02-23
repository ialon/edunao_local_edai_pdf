<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 *
 * @package    local_course_exporter
 * @copyright  2025 Edunao SAS (contact@edunao.com)
 * @author     Pierre FACQ <pierre.facq@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_course_exporter\helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Utility class for cleaning and normalizing PDF HTML content.
 */
class pdf_cleaner {

    // Special math characters and their TeX equivalents.
    private array $mathcharacters = [
        'α' => '\( \alpha \)',
        'β' => '\( \beta \)',
        'γ' => '\( \gamma \)',
        'δ' => '\( \delta \)',
        'ε' => '\( \epsilon \)',
        'ζ' => '\( \zeta \)',
        'η' => '\( \eta \)',
        'θ' => '\( \theta \)',
        'ι' => '\( \iota \)',
        'κ' => '\( \kappa \)',
        'λ' => '\( \lambda \)',
        'μ' => '\( \mu \)',
        'ν' => '\( \nu \)',
        'ξ' => '\( \xi \)',
        'ο' => '\( o \)',
        'π' => '\( \pi \)',
        'ρ' => '\( \rho \)',
        'σ' => '\( \sigma \)',
        'τ' => '\( \tau \)',
        'υ' => '\( \upsilon \)',
        'φ' => '\( \phi \)',
        'χ' => '\( \chi \)',
        'ψ' => '\( \psi \)',
        'ω' => '\( \omega \)',
        'Α' => '\( \Alpha \)',
        'Β' => '\( \Beta \)',
        'Γ' => '\( \Gamma \)',
        'Δ' => '\( \Delta \)',
        'Ε' => '\( \Epsilon \)',
        'Ζ' => '\( \Zeta \)',
        'Η' => '\( \Eta \)',
        'Θ' => '\( \Theta \)',
        'Ι' => '\( \Iota \)',
        'Κ' => '\( \Kappa \)',
        'Λ' => '\( \Lambda \)',
        'Μ' => '\( \Mu \)',
        'Ν' => '\( \Nu \)',
        'Ξ' => '\( \Xi \)',
        'Ο' => '\( \Omicron \)',
        'Π' => '\( \Pi \)',
        'Ρ' => '\( \Rho \)',
        'Σ' => '\( \Sigma \)',
        'Τ' => '\( \Tau \)',
        'Υ' => '\( \Upsilon \)',
        'Φ' => '\( \Phi \)',
        'Χ' => '\( \Chi \)',
        'Ψ' => '\( \Psi \)',
        'Ω' => '\( \Omega \)',
        '∑' => '\( \sum \)',
        '∏' => '\( \prod \)',
        '∫' => '\( \int \)',
        '∂' => '\( \partial \)',
        '∞' => '\( \infty \)',
        '∇' => '\( \nabla \)',
        '≈' => '\( \approx \)',
        '≠' => '\( \neq \)',
        '≡' => '\( \equiv \)',
        '≤' => '\( \leq \)',
        '≥' => '\( \geq \)',
        '⊂' => '\( \subset \)',
        '⊃' => '\( \supset \)',
        '⊆' => '\( \subseteq \)',
        '⊇' => '\( \supseteq \)',
        '∈' => '\( \in \)',
        '∉' => '\( \notin \)',
        '∪' => '\( \cup \)',
        '∩' => '\( \cap \)',
        '∧' => '\( \wedge \)',
        '∨' => '\( \vee \)',
        '¬' => '\( \neg \)',
        '∀' => '\( \forall \)',
        '∃' => '\( \exists \)',
        '∅' => '\( \emptyset \)',
        'ℕ' => '\( \mathbb{N} \)',
        'ℤ' => '\( \mathbb{Z} \)',
        'ℚ' => '\( \mathbb{Q} \)',
        'ℝ' => '\( \mathbb{R} \)',
        'ℂ' => '\( \mathbb{C} \)',
        'ℙ' => '\( \mathbb{P} \)',
        'ℵ' => '\( \aleph \)',
        'ℶ' => '\( \beth \)',
        'ℷ' => '\( \gimel \)',
        'ℸ' => '\( \daleth \)',
        '⊕' => '\( \oplus \)',
        '⊗' => '\( \otimes \)',
        '⊥' => '\( \perp \)',
        '∠' => '\( \angle \)',
        '∟' => '\( \measuredangle \)',
        '∡' => '\( \sphericalangle \)',
        '⊥' => '\( \perp \)',
        '∥' => '\( \parallel \)',
        '∦' => '\( \nparallel \)',
        '∴' => '\( \therefore \)',
        '∵' => '\( \because \)',
        '∷' => '\( \propto \)',
        '∸' => '\( \dotminus \)',
        '∹' => '\( \eqdot \)',
        '∺' => '\( \doteq \)',
        '∻' => '\( \doteqdot \)',
        '∼' => '\( \sim \)',
        '∽' => '\( \backsim \)',
        '∾' => '\( \backsimeq \)',
        '≀' => '\( \wr \)',
        '≁' => '\( \nsim \)',
        '≂' => '\( \eqsim \)',
        '≃' => '\( \simeq \)',
        '≄' => '\( \nsimeq \)',
        '≅' => '\( \cong \)',
        '≆' => '\( \ncong \)',
        '≇' => '\( \approx \)',
        '≉' => '\( \napprox \)',
        '≊' => '\( \approxeq \)',
        '≋' => '\( \approxident \)',
        '≌' => '\( \asymp \)',
        '≍' => '\( \Bumpeq \)',
        '≎' => '\( \bumpeq \)',
        '≏' => '\( \doteq \)',
        '≐' => '\( \doteqdot \)',
        '≑' => '\( \fallingdotseq \)',
        '≒' => '\( \risingdotseq \)',
        '≓' => '\( \eqcirc \)',
        '≔' => '\( \circeq \)',
        '≕' => '\( \triangleq \)',
        '≖' => '\( \eqslantgtr \)',
        '≗' => '\( \eqslantless \)',
        '≘' => '\( \lessgtr \)',
        '≙' => '\( \gtrless \)',
        '≚' => '\( \lesseqgtr \)',
        '≛' => '\( \gtreqless \)',
        '≜' => '\( \lesseqqgtr \)',
        '≝' => '\( \gtreqqless \)',
        '≞' => '\( \lessdot \)',
        '≟' => '\( \gtrdot \)',
        '≠' => '\( \neq \)',
        '≡' => '\( \equiv \)',
        '≢' => '\( \nequiv \)',
        '≣' => '\( \Equiv \)',
        '≤' => '\( \leq \)',
        '≥' => '\( \geq \)',
        '≦' => '\( \leqq \)',
        '≧' => '\( \geqq \)',
        '≨' => '\( \lneqq \)',
        '≩' => '\( \gneqq \)',
        '≪' => '\( \ll \)',
        '≫' => '\( \gg \)',
        '≬' => '\( \between \)',
        '≭' => '\( \notasymp \)',
        '≮' => '\( \nless \)',
        '≯' => '\( \ngtr \)',
        '≰' => '\( \nleq \)',
        '≱' => '\( \ngeq \)',
        '≲' => '\( \lesssim \)',
        '≳' => '\( \gtrsim \)',
        '≴' => '\( \nlesssim \)',
        '≵' => '\( \ngtrsim \)',
        '≶' => '\( \lessgtr \)',
        '≷' => '\( \gtrless \)',
        '≸' => '\( \nlessgtr \)',
        '≹' => '\( \ngtrless \)',
        '≺' => '\( \prec \)',
        '≻' => '\( \succ \)',
        '≼' => '\( \preccurlyeq \)',
        '≽' => '\( \succcurlyeq \)',
        '≾' => '\( \precsim \)',
        '≿' => '\( \succsim \)',
        '⊀' => '\( \nprec \)',
        '⊁' => '\( \nsucc \)',
        '⊂' => '\( \subset \)',
        '⊃' => '\( \supset \)',
        '⊄' => '\( \nsubset \)',
        '⊅' => '\( \nsupset \)',
        '⊆' => '\( \subseteq \)',
        '⊇' => '\( \supseteq \)',
        '⊈' => '\( \nsubseteq \)',
        '⊉' => '\( \nsupseteq \)',
        '⊊' => '\( \subsetneq \)',
        '⊋' => '\( \supsetneq \)',
        '⊌' => '\( \cupdot \)',
        '⊍' => '\( \uplus \)',
        '⊎' => '\( \sqcup \)',
        '⊏' => '\( \sqsubset \)',
        '⊐' => '\( \sqsupset \)',
        '⊑' => '\( \sqsubseteq \)',
        '⊒' => '\( \sqsupseteq \)',
        '⊓' => '\( \sqcap \)',
        '⊔' => '\( \sqcup \)',
        '⊕' => '\( \oplus \)',
        '⊖' => '\( \ominus \)',
        '⊗' => '\( \otimes \)',
        '⊘' => '\( \oslash \)',
        '⊙' => '\( \odot \)',
        '⊚' => '\( \circledcirc \)',
        '⊛' => '\( \circledast \)',
        '⊜' => '\( \circleddash \)',
        '⊝' => '\( \circledminus \)',
        '⊞' => '\( \boxplus \)',
        '⊟' => '\( \boxminus \)',
        '⊠' => '\( \boxtimes \)',
        '⊡' => '\( \boxdot \)',
        '⊢' => '\( \vdash \)',
        '⊣' => '\( \dashv \)',
        '⊤' => '\( \top \)',
        '⊥' => '\( \bot \)',
        '⊦' => '\( \models \)',
        '⊧' => '\( \vDash \)',
        '⊨' => '\( \Vdash \)',
        '⊩' => '\( \Vvdash \)',
        '⊪' => '\( \VDash \)',
        '⊫' => '\( \nvdash \)',
        '⊬' => '\( \nvDash \)',
        '⊭' => '\( \nVdash \)',
        '⊮' => '\( \nVDash \)',
        '⊯' => '\( \nVvdash \)',
        '⊰' => '\( \vartriangleleft \)',
        '⊱' => '\( \vartriangleright \)',
        '⊲' => '\( \triangleleft \)',
        '⊳' => '\( \triangleright \)',
        '⊴' => '\( \trianglelefteq \)',
        '⊵' => '\( \trianglerighteq \)',
        '⊶' => '\( \multimap \)',
        '⊷' => '\( \multimapinv \)',
        '⊸' => '\( \multimapdot \)',
        '⊹' => '\( \multimapdotinv \)',
        '⊺' => '\( \multimapdotdot \)',
        '⊻' => '\( \multimapdotdotinv \)',
        '⊼' => '\( \multimapdotdotdot \)',
        '⊽' => '\( \multimapdotdotdotinv \)',
        '⊾' => '\( \multimapdotdotdotdot \)',
        '⊿' => '\( \multimapdotdotdotdotinv \)',
        '⋀' => '\( \bigwedge \)',
        '⋁' => '\( \bigvee \)',
        '⋂' => '\( \bigcap \)',
        '⋃' => '\( \bigcup \)',
        '⋄' => '\( \diamond \)',
        '⋅' => '\( \cdot \)',
        '⋆' => '\( \star \)',
        '⋇' => '\( \divideontimes \)',
        '⋈' => '\( \bowtie \)',
        '⋉' => '\( \ltimes \)',
        '⋊' => '\( \rtimes \)',
        '⋋' => '\( \leftthreetimes \)',
        '⋌' => '\( \rightthreetimes \)',
        '⋍' => '\( \backsimeq \)',
        '⋎' => '\( \curlyvee \)',
        '⋏' => '\( \curlywedge \)',
        '⋐' => '\( \Subset \)',
        '⋑' => '\( \Supset \)',
        '⋒' => '\( \Cap \)',
        '⋓' => '\( \Cup \)',
        '⋔' => '\( \pitchfork \)',
        '⋕' => '\( \equalparallel \)',
        '⋖' => '\( \lessdot \)',
        '⋗' => '\( \gtrdot \)',
        '⋘' => '\( \lll \)',
        '⋙' => '\( \ggg \)',
        '⋚' => '\( \lesseqgtr \)',
        '⋛' => '\( \gtreqless \)',
        '⋜' => '\( \lesseqqgtr \)',
        '⋝' => '\( \gtreqqless \)',
        '⋞' => '\( \lessdot \)',
        '⋟' => '\( \gtrdot \)',
        '⋠' => '\( \nlessdot \)',
        '⋡' => '\( \ngtrdot \)',
        '⋢' => '\( \nlesseqgtr \)',
        '⋣' => '\( \ngtreqless \)',
        '⋤' => '\( \nlesseqqgtr \)',
        '⋥' => '\( \ngtreqqless \)',
        '⋦' => '\( \lessdot \)',
        '⋧' => '\( \gtrdot \)',
        '⋨' => '\( \nlessdot \)',
        '⋩' => '\( \ngtrdot \)',
        '⋪' => '\( \nlesseqgtr \)',
        '⋫' => '\( \ngtreqless \)',
        '⋬' => '\( \nlesseqqgtr \)',
        '⋭' => '\( \ngtreqqless \)',
        '⋮' => '\( \vdots \)',
        '⋯' => '\( \cdots \)',
        '⋰' => '\( \ddots \)',
        '⋱' => '\( \iddots \)',
        '⋲' => '\( \adots \)',
        '⋳' => '\( \ddots \)',
        '⋴' => '\( \iddots \)',
        '⋵' => '\( \adots \)',
        '⋶' => '\( \ddots \)',
        '⋷' => '\( \iddots \)',
        '⋸' => '\( \adots \)',
        '⋹' => '\( \ddots \)',
        '⋺' => '\( \iddots \)',
        '⋻' => '\( \adots \)',
        '⋼' => '\( \ddots \)',
        '⋽' => '\( \iddots \)',
        '⋾' => '\( \adots \)',
        '⋿' => '\( \ddots \)',
    ];

    // All emojis sorted by length in descending order.
    // https://charlottebuff.com/unicode/misc/emoji-length/
    private string $allemojis =
        // 10 characters
        "👨🏻‍❤️‍💋‍👨🏻 👨🏻‍❤️‍💋‍👨🏼 👨🏻‍❤️‍💋‍👨🏽 👨🏻‍❤️‍💋‍👨🏾 👨🏻‍❤️‍💋‍👨🏿 👨🏼‍❤️‍💋‍👨🏻 👨🏼‍❤️‍💋‍👨🏼 👨🏼‍❤️‍💋‍👨🏽 👨🏼‍❤️‍💋‍👨🏾 👨🏼‍❤️‍💋‍👨🏿 👨🏽‍❤️‍💋‍👨🏻 👨🏽‍❤️‍💋‍👨🏼 👨🏽‍❤️‍💋‍👨🏽 👨🏽‍❤️‍💋‍👨🏾 👨🏽‍❤️‍💋‍👨🏿 👨🏾‍❤️‍💋‍👨🏻 👨🏾‍❤️‍💋‍👨🏼 👨🏾‍❤️‍💋‍👨🏽 👨🏾‍❤️‍💋‍👨🏾 👨🏾‍❤️‍💋‍👨🏿 👨🏿‍❤️‍💋‍👨🏻 👨🏿‍❤️‍💋‍👨🏼 👨🏿‍❤️‍💋‍👨🏽 👨🏿‍❤️‍💋‍👨🏾 👨🏿‍❤️‍💋‍👨🏿 👩🏻‍❤️‍💋‍👨🏻 👩🏻‍❤️‍💋‍👨🏼 👩🏻‍❤️‍💋‍👨🏽 👩🏻‍❤️‍💋‍👨🏾 👩🏻‍❤️‍💋‍👨🏿 👩🏻‍❤️‍💋‍👩🏻 👩🏻‍❤️‍💋‍👩🏼 👩🏻‍❤️‍💋‍👩🏽 👩🏻‍❤️‍💋‍👩🏾 👩🏻‍❤️‍💋‍👩🏿 👩🏼‍❤️‍💋‍👨🏻 👩🏼‍❤️‍💋‍👨🏼 👩🏼‍❤️‍💋‍👨🏽 👩🏼‍❤️‍💋‍👨🏾 👩🏼‍❤️‍💋‍👨🏿 👩🏼‍❤️‍💋‍👩🏻 👩🏼‍❤️‍💋‍👩🏼 👩🏼‍❤️‍💋‍👩🏽 👩🏼‍❤️‍💋‍👩🏾 👩🏼‍❤️‍💋‍👩🏿 👩🏽‍❤️‍💋‍👨🏻 👩🏽‍❤️‍💋‍👨🏼 👩🏽‍❤️‍💋‍👨🏽 👩🏽‍❤️‍💋‍👨🏾 👩🏽‍❤️‍💋‍👨🏿 👩🏽‍❤️‍💋‍👩🏻 👩🏽‍❤️‍💋‍👩🏼 👩🏽‍❤️‍💋‍👩🏽 👩🏽‍❤️‍💋‍👩🏾 👩🏽‍❤️‍💋‍👩🏿 👩🏾‍❤️‍💋‍👨🏻 👩🏾‍❤️‍💋‍👨🏼 👩🏾‍❤️‍💋‍👨🏽 👩🏾‍❤️‍💋‍👨🏾 👩🏾‍❤️‍💋‍👨🏿 👩🏾‍❤️‍💋‍👩🏻 👩🏾‍❤️‍💋‍👩🏼 👩🏾‍❤️‍💋‍👩🏽 👩🏾‍❤️‍💋‍👩🏾 👩🏾‍❤️‍💋‍👩🏿 👩🏿‍❤️‍💋‍👨🏻 👩🏿‍❤️‍💋‍👨🏼 👩🏿‍❤️‍💋‍👨🏽 👩🏿‍❤️‍💋‍👨🏾 👩🏿‍❤️‍💋‍👨🏿 👩🏿‍❤️‍💋‍👩🏻 👩🏿‍❤️‍💋‍👩🏼 👩🏿‍❤️‍💋‍👩🏽 👩🏿‍❤️‍💋‍👩🏾 👩🏿‍❤️‍💋‍👩🏿 🧑🏻‍❤️‍💋‍🧑🏼 🧑🏻‍❤️‍💋‍🧑🏽 🧑🏻‍❤️‍💋‍🧑🏾 🧑🏻‍❤️‍💋‍🧑🏿 🧑🏼‍❤️‍💋‍🧑🏻 🧑🏼‍❤️‍💋‍🧑🏽 🧑🏼‍❤️‍💋‍🧑🏾 🧑🏼‍❤️‍💋‍🧑🏿 🧑🏽‍❤️‍💋‍🧑🏻 🧑🏽‍❤️‍💋‍🧑🏼 🧑🏽‍❤️‍💋‍🧑🏾 🧑🏽‍❤️‍💋‍🧑🏿 🧑🏾‍❤️‍💋‍🧑🏻 🧑🏾‍❤️‍💋‍🧑🏼 🧑🏾‍❤️‍💋‍🧑🏽 🧑🏾‍❤️‍💋‍🧑🏿 🧑🏿‍❤️‍💋‍🧑🏻 🧑🏿‍❤️‍💋‍🧑🏼 🧑🏿‍❤️‍💋‍🧑🏽 🧑🏿‍❤️‍💋‍🧑🏾" .
        // 8 characters
        " 🏃🏻‍♀️‍➡️ 🏃🏻‍♂️‍➡️ 🏃🏼‍♀️‍➡️ 🏃🏼‍♂️‍➡️ 🏃🏽‍♀️‍➡️ 🏃🏽‍♂️‍➡️ 🏃🏾‍♀️‍➡️ 🏃🏾‍♂️‍➡️ 🏃🏿‍♀️‍➡️ 🏃🏿‍♂️‍➡️ 👨‍❤️‍💋‍👨 👨🏻‍❤️‍👨🏻 👨🏻‍❤️‍👨🏼 👨🏻‍❤️‍👨🏽 👨🏻‍❤️‍👨🏾 👨🏻‍❤️‍👨🏿 👨🏼‍❤️‍👨🏻 👨🏼‍❤️‍👨🏼 👨🏼‍❤️‍👨🏽 👨🏼‍❤️‍👨🏾 👨🏼‍❤️‍👨🏿 👨🏽‍❤️‍👨🏻 👨🏽‍❤️‍👨🏼 👨🏽‍❤️‍👨🏽 👨🏽‍❤️‍👨🏾 👨🏽‍❤️‍👨🏿 👨🏾‍❤️‍👨🏻 👨🏾‍❤️‍👨🏼 👨🏾‍❤️‍👨🏽 👨🏾‍❤️‍👨🏾 👨🏾‍❤️‍👨🏿 👨🏿‍❤️‍👨🏻 👨🏿‍❤️‍👨🏼 👨🏿‍❤️‍👨🏽 👨🏿‍❤️‍👨🏾 👨🏿‍❤️‍👨🏿 👩‍❤️‍💋‍👨 👩‍❤️‍💋‍👩 👩🏻‍❤️‍👨🏻 👩🏻‍❤️‍👨🏼 👩🏻‍❤️‍👨🏽 👩🏻‍❤️‍👨🏾 👩🏻‍❤️‍👨🏿 👩🏻‍❤️‍👩🏻 👩🏻‍❤️‍👩🏼 👩🏻‍❤️‍👩🏽 👩🏻‍❤️‍👩🏾 👩🏻‍❤️‍👩🏿 👩🏼‍❤️‍👨🏻 👩🏼‍❤️‍👨🏼 👩🏼‍❤️‍👨🏽 👩🏼‍❤️‍👨🏾 👩🏼‍❤️‍👨🏿 👩🏼‍❤️‍👩🏻 👩🏼‍❤️‍👩🏼 👩🏼‍❤️‍👩🏽 👩🏼‍❤️‍👩🏾 👩🏼‍❤️‍👩🏿 👩🏽‍❤️‍👨🏻 👩🏽‍❤️‍👨🏼 👩🏽‍❤️‍👨🏽 👩🏽‍❤️‍👨🏾 👩🏽‍❤️‍👨🏿 👩🏽‍❤️‍👩🏻 👩🏽‍❤️‍👩🏼 👩🏽‍❤️‍👩🏽 👩🏽‍❤️‍👩🏾 👩🏽‍❤️‍👩🏿 👩🏾‍❤️‍👨🏻 👩🏾‍❤️‍👨🏼 👩🏾‍❤️‍👨🏽 👩🏾‍❤️‍👨🏾 👩🏾‍❤️‍👨🏿 👩🏾‍❤️‍👩🏻 👩🏾‍❤️‍👩🏼 👩🏾‍❤️‍👩🏽 👩🏾‍❤️‍👩🏾 👩🏾‍❤️‍👩🏿 👩🏿‍❤️‍👨🏻 👩🏿‍❤️‍👨🏼 👩🏿‍❤️‍👨🏽 👩🏿‍❤️‍👨🏾 👩🏿‍❤️‍👨🏿 👩🏿‍❤️‍👩🏻 👩🏿‍❤️‍👩🏼 👩🏿‍❤️‍👩🏽 👩🏿‍❤️‍👩🏾 👩🏿‍❤️‍👩🏿 🚶🏻‍♀️‍➡️ 🚶🏻‍♂️‍➡️ 🚶🏼‍♀️‍➡️ 🚶🏼‍♂️‍➡️ 🚶🏽‍♀️‍➡️ 🚶🏽‍♂️‍➡️ 🚶🏾‍♀️‍➡️ 🚶🏾‍♂️‍➡️ 🚶🏿‍♀️‍➡️ 🚶🏿‍♂️‍➡️ 🧎🏻‍♀️‍➡️ 🧎🏻‍♂️‍➡️ 🧎🏼‍♀️‍➡️ 🧎🏼‍♂️‍➡️ 🧎🏽‍♀️‍➡️ 🧎🏽‍♂️‍➡️ 🧎🏾‍♀️‍➡️ 🧎🏾‍♂️‍➡️ 🧎🏿‍♀️‍➡️ 🧎🏿‍♂️‍➡️ 🧑🏻‍❤️‍🧑🏼 🧑🏻‍❤️‍🧑🏽 🧑🏻‍❤️‍🧑🏾 🧑🏻‍❤️‍🧑🏿 🧑🏼‍❤️‍🧑🏻 🧑🏼‍❤️‍🧑🏽 🧑🏼‍❤️‍🧑🏾 🧑🏼‍❤️‍🧑🏿 🧑🏽‍❤️‍🧑🏻 🧑🏽‍❤️‍🧑🏼 🧑🏽‍❤️‍🧑🏾 🧑🏽‍❤️‍🧑🏿 🧑🏾‍❤️‍🧑🏻 🧑🏾‍❤️‍🧑🏼 🧑🏾‍❤️‍🧑🏽 🧑🏾‍❤️‍🧑🏿 🧑🏿‍❤️‍🧑🏻 🧑🏿‍❤️‍🧑🏼 🧑🏿‍❤️‍🧑🏽 🧑🏿‍❤️‍🧑🏾" .
        // 7 characters
        " 🏃‍♀️‍➡️ 🏃‍♂️‍➡️ 🏴󠁧󠁢󠁥󠁮󠁧󠁿 🏴󠁧󠁢󠁳󠁣󠁴󠁿 🏴󠁧󠁢󠁷󠁬󠁳󠁿 👨‍👨‍👦‍👦 👨‍👨‍👧‍👦 👨‍👨‍👧‍👧 👨‍👩‍👦‍👦 👨‍👩‍👧‍👦 👨‍👩‍👧‍👧 👨🏻‍🤝‍👨🏼 👨🏻‍🤝‍👨🏽 👨🏻‍🤝‍👨🏾 👨🏻‍🤝‍👨🏿 👨🏻‍🦯‍➡️ 👨🏻‍🦼‍➡️ 👨🏻‍🦽‍➡️ 👨🏼‍🤝‍👨🏻 👨🏼‍🤝‍👨🏽 👨🏼‍🤝‍👨🏾 👨🏼‍🤝‍👨🏿 👨🏼‍🦯‍➡️ 👨🏼‍🦼‍➡️ 👨🏼‍🦽‍➡️ 👨🏽‍🤝‍👨🏻 👨🏽‍🤝‍👨🏼 👨🏽‍🤝‍👨🏾 👨🏽‍🤝‍👨🏿 👨🏽‍🦯‍➡️ 👨🏽‍🦼‍➡️ 👨🏽‍🦽‍➡️ 👨🏾‍🤝‍👨🏻 👨🏾‍🤝‍👨🏼 👨🏾‍🤝‍👨🏽 👨🏾‍🤝‍👨🏿 👨🏾‍🦯‍➡️ 👨🏾‍🦼‍➡️ 👨🏾‍🦽‍➡️ 👨🏿‍🤝‍👨🏻 👨🏿‍🤝‍👨🏼 👨🏿‍🤝‍👨🏽 👨🏿‍🤝‍👨🏾 👨🏿‍🦯‍➡️ 👨🏿‍🦼‍➡️ 👨🏿‍🦽‍➡️ 👩‍👩‍👦‍👦 👩‍👩‍👧‍👦 👩‍👩‍👧‍👧 👩🏻‍🤝‍👨🏼 👩🏻‍🤝‍👨🏽 👩🏻‍🤝‍👨🏾 👩🏻‍🤝‍👨🏿 👩🏻‍🤝‍👩🏼 👩🏻‍🤝‍👩🏽 👩🏻‍🤝‍👩🏾 👩🏻‍🤝‍👩🏿 👩🏻‍🦯‍➡️ 👩🏻‍🦼‍➡️ 👩🏻‍🦽‍➡️ 👩🏼‍🤝‍👨🏻 👩🏼‍🤝‍👨🏽 👩🏼‍🤝‍👨🏾 👩🏼‍🤝‍👨🏿 👩🏼‍🤝‍👩🏻 👩🏼‍🤝‍👩🏽 👩🏼‍🤝‍👩🏾 👩🏼‍🤝‍👩🏿 👩🏼‍🦯‍➡️ 👩🏼‍🦼‍➡️ 👩🏼‍🦽‍➡️ 👩🏽‍🤝‍👨🏻 👩🏽‍🤝‍👨🏼 👩🏽‍🤝‍👨🏾 👩🏽‍🤝‍👨🏿 👩🏽‍🤝‍👩🏻 👩🏽‍🤝‍👩🏼 👩🏽‍🤝‍👩🏾 👩🏽‍🤝‍👩🏿 👩🏽‍🦯‍➡️ 👩🏽‍🦼‍➡️ 👩🏽‍🦽‍➡️ 👩🏾‍🤝‍👨🏻 👩🏾‍🤝‍👨🏼 👩🏾‍🤝‍👨🏽 👩🏾‍🤝‍👨🏿 👩🏾‍🤝‍👩🏻 👩🏾‍🤝‍👩🏼 👩🏾‍🤝‍👩🏽 👩🏾‍🤝‍👩🏿 👩🏾‍🦯‍➡️ 👩🏾‍🦼‍➡️ 👩🏾‍🦽‍➡️ 👩🏿‍🤝‍👨🏻 👩🏿‍🤝‍👨🏼 👩🏿‍🤝‍👨🏽 👩🏿‍🤝‍👨🏾 👩🏿‍🤝‍👩🏻 👩🏿‍🤝‍👩🏼 👩🏿‍🤝‍👩🏽 👩🏿‍🤝‍👩🏾 👩🏿‍🦯‍➡️ 👩🏿‍🦼‍➡️ 👩🏿‍🦽‍➡️ 🚶‍♀️‍➡️ 🚶‍♂️‍➡️ 🧎‍♀️‍➡️ 🧎‍♂️‍➡️ 🧑‍🧑‍🧒‍🧒 🧑🏻‍🤝‍🧑🏻 🧑🏻‍🤝‍🧑🏼 🧑🏻‍🤝‍🧑🏽 🧑🏻‍🤝‍🧑🏾 🧑🏻‍🤝‍🧑🏿 🧑🏻‍🦯‍➡️ 🧑🏻‍🦼‍➡️ 🧑🏻‍🦽‍➡️ 🧑🏼‍🤝‍🧑🏻 🧑🏼‍🤝‍🧑🏼 🧑🏼‍🤝‍🧑🏽 🧑🏼‍🤝‍🧑🏾 🧑🏼‍🤝‍🧑🏿 🧑🏼‍🦯‍➡️ 🧑🏼‍🦼‍➡️ 🧑🏼‍🦽‍➡️ 🧑🏽‍🤝‍🧑🏻 🧑🏽‍🤝‍🧑🏼 🧑🏽‍🤝‍🧑🏽 🧑🏽‍🤝‍🧑🏾 🧑🏽‍🤝‍🧑🏿 🧑🏽‍🦯‍➡️ 🧑🏽‍🦼‍➡️ 🧑🏽‍🦽‍➡️ 🧑🏾‍🤝‍🧑🏻 🧑🏾‍🤝‍🧑🏼 🧑🏾‍🤝‍🧑🏽 🧑🏾‍🤝‍🧑🏾 🧑🏾‍🤝‍🧑🏿 🧑🏾‍🦯‍➡️ 🧑🏾‍🦼‍➡️ 🧑🏾‍🦽‍➡️ 🧑🏿‍🤝‍🧑🏻 🧑🏿‍🤝‍🧑🏼 🧑🏿‍🤝‍🧑🏽 🧑🏿‍🤝‍🧑🏾 🧑🏿‍🤝‍🧑🏿 🧑🏿‍🦯‍➡️ 🧑🏿‍🦼‍➡️ 🧑🏿‍🦽‍➡️" .
        // 6 characters
        " 👨‍❤️‍👨 👨‍🦯‍➡️ 👨‍🦼‍➡️ 👨‍🦽‍➡️ 👩‍❤️‍👨 👩‍❤️‍👩 👩‍🦯‍➡️ 👩‍🦼‍➡️ 👩‍🦽‍➡️ 🧑‍🦯‍➡️ 🧑‍🦼‍➡️ 🧑‍🦽‍➡️" .
        // 5 characters
        " ⛹️‍♀️ ⛹️‍♂️ ⛹🏻‍♀️ ⛹🏻‍♂️ ⛹🏼‍♀️ ⛹🏼‍♂️ ⛹🏽‍♀️ ⛹🏽‍♂️ ⛹🏾‍♀️ ⛹🏾‍♂️ ⛹🏿‍♀️ ⛹🏿‍♂️ 🏃🏻‍♀️ 🏃🏻‍♂️ 🏃🏻‍➡️ 🏃🏼‍♀️ 🏃🏼‍♂️ 🏃🏼‍➡️ 🏃🏽‍♀️ 🏃🏽‍♂️ 🏃🏽‍➡️ 🏃🏾‍♀️ 🏃🏾‍♂️ 🏃🏾‍➡️ 🏃🏿‍♀️ 🏃🏿‍♂️ 🏃🏿‍➡️ 🏄🏻‍♀️ 🏄🏻‍♂️ 🏄🏼‍♀️ 🏄🏼‍♂️ 🏄🏽‍♀️ 🏄🏽‍♂️ 🏄🏾‍♀️ 🏄🏾‍♂️ 🏄🏿‍♀️ 🏄🏿‍♂️ 🏊🏻‍♀️ 🏊🏻‍♂️ 🏊🏼‍♀️ 🏊🏼‍♂️ 🏊🏽‍♀️ 🏊🏽‍♂️ 🏊🏾‍♀️ 🏊🏾‍♂️ 🏊🏿‍♀️ 🏊🏿‍♂️ 🏋️‍♀️ 🏋️‍♂️ 🏋🏻‍♀️ 🏋🏻‍♂️ 🏋🏼‍♀️ 🏋🏼‍♂️ 🏋🏽‍♀️ 🏋🏽‍♂️ 🏋🏾‍♀️ 🏋🏾‍♂️ 🏋🏿‍♀️ 🏋🏿‍♂️ 🏌️‍♀️ 🏌️‍♂️ 🏌🏻‍♀️ 🏌🏻‍♂️ 🏌🏼‍♀️ 🏌🏼‍♂️ 🏌🏽‍♀️ 🏌🏽‍♂️ 🏌🏾‍♀️ 🏌🏾‍♂️ 🏌🏿‍♀️ 🏌🏿‍♂️ 🏳️‍⚧️ 👁️‍🗨️ 👨‍👦‍👦 👨‍👧‍👦 👨‍👧‍👧 👨‍👨‍👦 👨‍👨‍👧 👨‍👩‍👦 👨‍👩‍👧 👨🏻‍⚕️ 👨🏻‍⚖️ 👨🏻‍✈️ 👨🏼‍⚕️ 👨🏼‍⚖️ 👨🏼‍✈️ 👨🏽‍⚕️ 👨🏽‍⚖️ 👨🏽‍✈️ 👨🏾‍⚕️ 👨🏾‍⚖️ 👨🏾‍✈️ 👨🏿‍⚕️ 👨🏿‍⚖️ 👨🏿‍✈️ 👩‍👦‍👦 👩‍👧‍👦 👩‍👧‍👧 👩‍👩‍👦 👩‍👩‍👧 👩🏻‍⚕️ 👩🏻‍⚖️ 👩🏻‍✈️ 👩🏼‍⚕️ 👩🏼‍⚖️ 👩🏼‍✈️ 👩🏽‍⚕️ 👩🏽‍⚖️ 👩🏽‍✈️ 👩🏾‍⚕️ 👩🏾‍⚖️ 👩🏾‍✈️ 👩🏿‍⚕️ 👩🏿‍⚖️ 👩🏿‍✈️ 👮🏻‍♀️ 👮🏻‍♂️ 👮🏼‍♀️ 👮🏼‍♂️ 👮🏽‍♀️ 👮🏽‍♂️ 👮🏾‍♀️ 👮🏾‍♂️ 👮🏿‍♀️ 👮🏿‍♂️ 👰🏻‍♀️ 👰🏻‍♂️ 👰🏼‍♀️ 👰🏼‍♂️ 👰🏽‍♀️ 👰🏽‍♂️ 👰🏾‍♀️ 👰🏾‍♂️ 👰🏿‍♀️ 👰🏿‍♂️ 👱🏻‍♀️ 👱🏻‍♂️ 👱🏼‍♀️ 👱🏼‍♂️ 👱🏽‍♀️ 👱🏽‍♂️ 👱🏾‍♀️ 👱🏾‍♂️ 👱🏿‍♀️ 👱🏿‍♂️ 👳🏻‍♀️ 👳🏻‍♂️ 👳🏼‍♀️ 👳🏼‍♂️ 👳🏽‍♀️ 👳🏽‍♂️ 👳🏾‍♀️ 👳🏾‍♂️ 👳🏿‍♀️ 👳🏿‍♂️ 👷🏻‍♀️ 👷🏻‍♂️ 👷🏼‍♀️ 👷🏼‍♂️ 👷🏽‍♀️ 👷🏽‍♂️ 👷🏾‍♀️ 👷🏾‍♂️ 👷🏿‍♀️ 👷🏿‍♂️ 💁🏻‍♀️ 💁🏻‍♂️ 💁🏼‍♀️ 💁🏼‍♂️ 💁🏽‍♀️ 💁🏽‍♂️ 💁🏾‍♀️ 💁🏾‍♂️ 💁🏿‍♀️ 💁🏿‍♂️ 💂🏻‍♀️ 💂🏻‍♂️ 💂🏼‍♀️ 💂🏼‍♂️ 💂🏽‍♀️ 💂🏽‍♂️ 💂🏾‍♀️ 💂🏾‍♂️ 💂🏿‍♀️ 💂🏿‍♂️ 💆🏻‍♀️ 💆🏻‍♂️ 💆🏼‍♀️ 💆🏼‍♂️ 💆🏽‍♀️ 💆🏽‍♂️ 💆🏾‍♀️ 💆🏾‍♂️ 💆🏿‍♀️ 💆🏿‍♂️ 💇🏻‍♀️ 💇🏻‍♂️ 💇🏼‍♀️ 💇🏼‍♂️ 💇🏽‍♀️ 💇🏽‍♂️ 💇🏾‍♀️ 💇🏾‍♂️ 💇🏿‍♀️ 💇🏿‍♂️ 🕵️‍♀️ 🕵️‍♂️ 🕵🏻‍♀️ 🕵🏻‍♂️ 🕵🏼‍♀️ 🕵🏼‍♂️ 🕵🏽‍♀️ 🕵🏽‍♂️ 🕵🏾‍♀️ 🕵🏾‍♂️ 🕵🏿‍♀️ 🕵🏿‍♂️ 🙅🏻‍♀️ 🙅🏻‍♂️ 🙅🏼‍♀️ 🙅🏼‍♂️ 🙅🏽‍♀️ 🙅🏽‍♂️ 🙅🏾‍♀️ 🙅🏾‍♂️ 🙅🏿‍♀️ 🙅🏿‍♂️ 🙆🏻‍♀️ 🙆🏻‍♂️ 🙆🏼‍♀️ 🙆🏼‍♂️ 🙆🏽‍♀️ 🙆🏽‍♂️ 🙆🏾‍♀️ 🙆🏾‍♂️ 🙆🏿‍♀️ 🙆🏿‍♂️ 🙇🏻‍♀️ 🙇🏻‍♂️ 🙇🏼‍♀️ 🙇🏼‍♂️ 🙇🏽‍♀️ 🙇🏽‍♂️ 🙇🏾‍♀️ 🙇🏾‍♂️ 🙇🏿‍♀️ 🙇🏿‍♂️ 🙋🏻‍♀️ 🙋🏻‍♂️ 🙋🏼‍♀️ 🙋🏼‍♂️ 🙋🏽‍♀️ 🙋🏽‍♂️ 🙋🏾‍♀️ 🙋🏾‍♂️ 🙋🏿‍♀️ 🙋🏿‍♂️ 🙍🏻‍♀️ 🙍🏻‍♂️ 🙍🏼‍♀️ 🙍🏼‍♂️ 🙍🏽‍♀️ 🙍🏽‍♂️ 🙍🏾‍♀️ 🙍🏾‍♂️ 🙍🏿‍♀️ 🙍🏿‍♂️ 🙎🏻‍♀️ 🙎🏻‍♂️ 🙎🏼‍♀️ 🙎🏼‍♂️ 🙎🏽‍♀️ 🙎🏽‍♂️ 🙎🏾‍♀️ 🙎🏾‍♂️ 🙎🏿‍♀️ 🙎🏿‍♂️ 🚣🏻‍♀️ 🚣🏻‍♂️ 🚣🏼‍♀️ 🚣🏼‍♂️ 🚣🏽‍♀️ 🚣🏽‍♂️ 🚣🏾‍♀️ 🚣🏾‍♂️ 🚣🏿‍♀️ 🚣🏿‍♂️ 🚴🏻‍♀️ 🚴🏻‍♂️ 🚴🏼‍♀️ 🚴🏼‍♂️ 🚴🏽‍♀️ 🚴🏽‍♂️ 🚴🏾‍♀️ 🚴🏾‍♂️ 🚴🏿‍♀️ 🚴🏿‍♂️ 🚵🏻‍♀️ 🚵🏻‍♂️ 🚵🏼‍♀️ 🚵🏼‍♂️ 🚵🏽‍♀️ 🚵🏽‍♂️ 🚵🏾‍♀️ 🚵🏾‍♂️ 🚵🏿‍♀️ 🚵🏿‍♂️ 🚶🏻‍♀️ 🚶🏻‍♂️ 🚶🏻‍➡️ 🚶🏼‍♀️ 🚶🏼‍♂️ 🚶🏼‍➡️ 🚶🏽‍♀️ 🚶🏽‍♂️ 🚶🏽‍➡️ 🚶🏾‍♀️ 🚶🏾‍♂️ 🚶🏾‍➡️ 🚶🏿‍♀️ 🚶🏿‍♂️ 🚶🏿‍➡️ 🤦🏻‍♀️ 🤦🏻‍♂️ 🤦🏼‍♀️ 🤦🏼‍♂️ 🤦🏽‍♀️ 🤦🏽‍♂️ 🤦🏾‍♀️ 🤦🏾‍♂️ 🤦🏿‍♀️ 🤦🏿‍♂️ 🤵🏻‍♀️ 🤵🏻‍♂️ 🤵🏼‍♀️ 🤵🏼‍♂️ 🤵🏽‍♀️ 🤵🏽‍♂️ 🤵🏾‍♀️ 🤵🏾‍♂️ 🤵🏿‍♀️ 🤵🏿‍♂️ 🤷🏻‍♀️ 🤷🏻‍♂️ 🤷🏼‍♀️ 🤷🏼‍♂️ 🤷🏽‍♀️ 🤷🏽‍♂️ 🤷🏾‍♀️ 🤷🏾‍♂️ 🤷🏿‍♀️ 🤷🏿‍♂️ 🤸🏻‍♀️ 🤸🏻‍♂️ 🤸🏼‍♀️ 🤸🏼‍♂️ 🤸🏽‍♀️ 🤸🏽‍♂️ 🤸🏾‍♀️ 🤸🏾‍♂️ 🤸🏿‍♀️ 🤸🏿‍♂️ 🤹🏻‍♀️ 🤹🏻‍♂️ 🤹🏼‍♀️ 🤹🏼‍♂️ 🤹🏽‍♀️ 🤹🏽‍♂️ 🤹🏾‍♀️ 🤹🏾‍♂️ 🤹🏿‍♀️ 🤹🏿‍♂️ 🤽🏻‍♀️ 🤽🏻‍♂️ 🤽🏼‍♀️ 🤽🏼‍♂️ 🤽🏽‍♀️ 🤽🏽‍♂️ 🤽🏾‍♀️ 🤽🏾‍♂️ 🤽🏿‍♀️ 🤽🏿‍♂️ 🤾🏻‍♀️ 🤾🏻‍♂️ 🤾🏼‍♀️ 🤾🏼‍♂️ 🤾🏽‍♀️ 🤾🏽‍♂️ 🤾🏾‍♀️ 🤾🏾‍♂️ 🤾🏿‍♀️ 🤾🏿‍♂️ 🦸🏻‍♀️ 🦸🏻‍♂️ 🦸🏼‍♀️ 🦸🏼‍♂️ 🦸🏽‍♀️ 🦸🏽‍♂️ 🦸🏾‍♀️ 🦸🏾‍♂️ 🦸🏿‍♀️ 🦸🏿‍♂️ 🦹🏻‍♀️ 🦹🏻‍♂️ 🦹🏼‍♀️ 🦹🏼‍♂️ 🦹🏽‍♀️ 🦹🏽‍♂️ 🦹🏾‍♀️ 🦹🏾‍♂️ 🦹🏿‍♀️ 🦹🏿‍♂️ 🧍🏻‍♀️ 🧍🏻‍♂️ 🧍🏼‍♀️ 🧍🏼‍♂️ 🧍🏽‍♀️ 🧍🏽‍♂️ 🧍🏾‍♀️ 🧍🏾‍♂️ 🧍🏿‍♀️ 🧍🏿‍♂️ 🧎🏻‍♀️ 🧎🏻‍♂️ 🧎🏻‍➡️ 🧎🏼‍♀️ 🧎🏼‍♂️ 🧎🏼‍➡️ 🧎🏽‍♀️ 🧎🏽‍♂️ 🧎🏽‍➡️ 🧎🏾‍♀️ 🧎🏾‍♂️ 🧎🏾‍➡️ 🧎🏿‍♀️ 🧎🏿‍♂️ 🧎🏿‍➡️ 🧏🏻‍♀️ 🧏🏻‍♂️ 🧏🏼‍♀️ 🧏🏼‍♂️ 🧏🏽‍♀️ 🧏🏽‍♂️ 🧏🏾‍♀️ 🧏🏾‍♂️ 🧏🏿‍♀️ 🧏🏿‍♂️ 🧑‍🤝‍🧑 🧑‍🧑‍🧒 🧑‍🧒‍🧒 🧑🏻‍⚕️ 🧑🏻‍⚖️ 🧑🏻‍✈️ 🧑🏼‍⚕️ 🧑🏼‍⚖️ 🧑🏼‍✈️ 🧑🏽‍⚕️ 🧑🏽‍⚖️ 🧑🏽‍✈️ 🧑🏾‍⚕️ 🧑🏾‍⚖️ 🧑🏾‍✈️ 🧑🏿‍⚕️ 🧑🏿‍⚖️ 🧑🏿‍✈️ 🧔🏻‍♀️ 🧔🏻‍♂️ 🧔🏼‍♀️ 🧔🏼‍♂️ 🧔🏽‍♀️ 🧔🏽‍♂️ 🧔🏾‍♀️ 🧔🏾‍♂️ 🧔🏿‍♀️ 🧔🏿‍♂️ 🧖🏻‍♀️ 🧖🏻‍♂️ 🧖🏼‍♀️ 🧖🏼‍♂️ 🧖🏽‍♀️ 🧖🏽‍♂️ 🧖🏾‍♀️ 🧖🏾‍♂️ 🧖🏿‍♀️ 🧖🏿‍♂️ 🧗🏻‍♀️ 🧗🏻‍♂️ 🧗🏼‍♀️ 🧗🏼‍♂️ 🧗🏽‍♀️ 🧗🏽‍♂️ 🧗🏾‍♀️ 🧗🏾‍♂️ 🧗🏿‍♀️ 🧗🏿‍♂️ 🧘🏻‍♀️ 🧘🏻‍♂️ 🧘🏼‍♀️ 🧘🏼‍♂️ 🧘🏽‍♀️ 🧘🏽‍♂️ 🧘🏾‍♀️ 🧘🏾‍♂️ 🧘🏿‍♀️ 🧘🏿‍♂️ 🧙🏻‍♀️ 🧙🏻‍♂️ 🧙🏼‍♀️ 🧙🏼‍♂️ 🧙🏽‍♀️ 🧙🏽‍♂️ 🧙🏾‍♀️ 🧙🏾‍♂️ 🧙🏿‍♀️ 🧙🏿‍♂️ 🧚🏻‍♀️ 🧚🏻‍♂️ 🧚🏼‍♀️ 🧚🏼‍♂️ 🧚🏽‍♀️ 🧚🏽‍♂️ 🧚🏾‍♀️ 🧚🏾‍♂️ 🧚🏿‍♀️ 🧚🏿‍♂️ 🧛🏻‍♀️ 🧛🏻‍♂️ 🧛🏼‍♀️ 🧛🏼‍♂️ 🧛🏽‍♀️ 🧛🏽‍♂️ 🧛🏾‍♀️ 🧛🏾‍♂️ 🧛🏿‍♀️ 🧛🏿‍♂️ 🧜🏻‍♀️ 🧜🏻‍♂️ 🧜🏼‍♀️ 🧜🏼‍♂️ 🧜🏽‍♀️ 🧜🏽‍♂️ 🧜🏾‍♀️ 🧜🏾‍♂️ 🧜🏿‍♀️ 🧜🏿‍♂️ 🧝🏻‍♀️ 🧝🏻‍♂️ 🧝🏼‍♀️ 🧝🏼‍♂️ 🧝🏽‍♀️ 🧝🏽‍♂️ 🧝🏾‍♀️ 🧝🏾‍♂️ 🧝🏿‍♀️ 🧝🏿‍♂️ 🫱🏻‍🫲🏼 🫱🏻‍🫲🏽 🫱🏻‍🫲🏾 🫱🏻‍🫲🏿 🫱🏼‍🫲🏻 🫱🏼‍🫲🏽 🫱🏼‍🫲🏾 🫱🏼‍🫲🏿 🫱🏽‍🫲🏻 🫱🏽‍🫲🏼 🫱🏽‍🫲🏾 🫱🏽‍🫲🏿 🫱🏾‍🫲🏻 🫱🏾‍🫲🏼 🫱🏾‍🫲🏽 🫱🏾‍🫲🏿 🫱🏿‍🫲🏻 🫱🏿‍🫲🏼 🫱🏿‍🫲🏽 🫱🏿‍🫲🏾" .
        " 🏄️‍♀️ 🏄️‍♂️ 🏊️‍♀️ 🏊️‍♂️ 👨🏻‍🎓️ 👨🏻‍🏭️ 👨🏻‍💻️ 👨🏼‍🎓️ 👨🏼‍🏭️ 👨🏼‍💻️ 👨🏽‍🎓️ 👨🏽‍🏭️ 👨🏽‍💻️ 👨🏾‍🎓️ 👨🏾‍🏭️ 👨🏾‍💻️ 👨🏿‍🎓️ 👨🏿‍🏭️ 👨🏿‍💻️ 👩🏻‍🎓️ 👩🏻‍🏭️ 👩🏻‍💻️ 👩🏼‍🎓️ 👩🏼‍🏭️ 👩🏼‍💻️ 👩🏽‍🎓️ 👩🏽‍🏭️ 👩🏽‍💻️ 👩🏾‍🎓️ 👩🏾‍🏭️ 👩🏾‍💻️ 👩🏿‍🎓️ 👩🏿‍🏭️ 👩🏿‍💻️ 🧑🏻‍🎓️ 🧑🏻‍🏭️ 🧑🏻‍💻️ 🧑🏼‍🎓️ 🧑🏼‍🏭️ 🧑🏼‍💻️ 🧑🏽‍🎓️ 🧑🏽‍🏭️ 🧑🏽‍💻️ 🧑🏾‍🎓️ 🧑🏾‍🏭️ 🧑🏾‍💻️ 🧑🏿‍🎓️ 🧑🏿‍🏭️ 🧑🏿‍💻️" .
        " 🐈️‍⬛️ 🐦️‍⬛️" .
        // 4 characters
        " ⛓️‍💥 ❤️‍🔥 ❤️‍🩹 🏃‍♀️ 🏃‍♂️ 🏃‍➡️ 🏳️‍🌈 🏴‍☠️ 🐻‍❄️ 👨‍⚕️ 👨‍⚖️ 👨‍✈️ 👨🏻‍🌾 👨🏻‍🍳 👨🏻‍🍼 👨🏻‍🎤 👨🏻‍🎨 👨🏻‍🏫 👨🏻‍💼 👨🏻‍🔧 👨🏻‍🔬 👨🏻‍🚀 👨🏻‍🚒 👨🏻‍🦯 👨🏻‍🦰 👨🏻‍🦱 👨🏻‍🦲 👨🏻‍🦳 👨🏻‍🦼 👨🏻‍🦽 👨🏼‍🌾 👨🏼‍🍳 👨🏼‍🍼 👨🏼‍🎤 👨🏼‍🎨 👨🏼‍🏫 👨🏼‍💼 👨🏼‍🔧 👨🏼‍🔬 👨🏼‍🚀 👨🏼‍🚒 👨🏼‍🦯 👨🏼‍🦰 👨🏼‍🦱 👨🏼‍🦲 👨🏼‍🦳 👨🏼‍🦼 👨🏼‍🦽 👨🏽‍🌾 👨🏽‍🍳 👨🏽‍🍼 👨🏽‍🎤 👨🏽‍🎨 👨🏽‍🏫 👨🏽‍💼 👨🏽‍🔧 👨🏽‍🔬 👨🏽‍🚀 👨🏽‍🚒 👨🏽‍🦯 👨🏽‍🦰 👨🏽‍🦱 👨🏽‍🦲 👨🏽‍🦳 👨🏽‍🦼 👨🏽‍🦽 👨🏾‍🌾 👨🏾‍🍳 👨🏾‍🍼 👨🏾‍🎤 👨🏾‍🎨 👨🏾‍🏫 👨🏾‍💼 👨🏾‍🔧 👨🏾‍🔬 👨🏾‍🚀 👨🏾‍🚒 👨🏾‍🦯 👨🏾‍🦰 👨🏾‍🦱 👨🏾‍🦲 👨🏾‍🦳 👨🏾‍🦼 👨🏾‍🦽 👨🏿‍🌾 👨🏿‍🍳 👨🏿‍🍼 👨🏿‍🎤 👨🏿‍🎨 👨🏿‍🏫 👨🏿‍💼 👨🏿‍🔧 👨🏿‍🔬 👨🏿‍🚀 👨🏿‍🚒 👨🏿‍🦯 👨🏿‍🦰 👨🏿‍🦱 👨🏿‍🦲 👨🏿‍🦳 👨🏿‍🦼 👨🏿‍🦽 👩‍⚕️ 👩‍⚖️ 👩‍✈️ 👩🏻‍🌾 👩🏻‍🍳 👩🏻‍🍼 👩🏻‍🎤 👩🏻‍🎨 👩🏻‍🏫 👩🏻‍💼 👩🏻‍🔧 👩🏻‍🔬 👩🏻‍🚀 👩🏻‍🚒 👩🏻‍🦯 👩🏻‍🦰 👩🏻‍🦱 👩🏻‍🦲 👩🏻‍🦳 👩🏻‍🦼 👩🏻‍🦽 👩🏼‍🌾 👩🏼‍🍳 👩🏼‍🍼 👩🏼‍🎤 👩🏼‍🎨 👩🏼‍🏫 👩🏼‍💼 👩🏼‍🔧 👩🏼‍🔬 👩🏼‍🚀 👩🏼‍🚒 👩🏼‍🦯 👩🏼‍🦰 👩🏼‍🦱 👩🏼‍🦲 👩🏼‍🦳 👩🏼‍🦼 👩🏼‍🦽 👩🏽‍🌾 👩🏽‍🍳 👩🏽‍🍼 👩🏽‍🎤 👩🏽‍🎨 👩🏽‍🏫 👩🏽‍💼 👩🏽‍🔧 👩🏽‍🔬 👩🏽‍🚀 👩🏽‍🚒 👩🏽‍🦯 👩🏽‍🦰 👩🏽‍🦱 👩🏽‍🦲 👩🏽‍🦳 👩🏽‍🦼 👩🏽‍🦽 👩🏾‍🌾 👩🏾‍🍳 👩🏾‍🍼 👩🏾‍🎤 👩🏾‍🎨 👩🏾‍🏫 👩🏾‍💼 👩🏾‍🔧 👩🏾‍🔬 👩🏾‍🚀 👩🏾‍🚒 👩🏾‍🦯 👩🏾‍🦰 👩🏾‍🦱 👩🏾‍🦲 👩🏾‍🦳 👩🏾‍🦼 👩🏾‍🦽 👩🏿‍🌾 👩🏿‍🍳 👩🏿‍🍼 👩🏿‍🎤 👩🏿‍🎨 👩🏿‍🏫 👩🏿‍💼 👩🏿‍🔧 👩🏿‍🔬 👩🏿‍🚀 👩🏿‍🚒 👩🏿‍🦯 👩🏿‍🦰 👩🏿‍🦱 👩🏿‍🦲 👩🏿‍🦳 👩🏿‍🦼 👩🏿‍🦽 👮‍♀️ 👮‍♂️ 👯‍♀️ 👯‍♂️ 👰‍♀️ 👰‍♂️ 👱‍♀️ 👱‍♂️ 👳‍♀️ 👳‍♂️ 👷‍♀️ 👷‍♂️ 💁‍♀️ 💁‍♂️ 💂‍♀️ 💂‍♂️ 💆‍♀️ 💆‍♂️ 💇‍♀️ 💇‍♂️ 😶‍🌫️ 🙂‍↔️ 🙂‍↕️ 🙅‍♀️ 🙅‍♂️ 🙆‍♀️ 🙆‍♂️ 🙇‍♀️ 🙇‍♂️ 🙋‍♀️ 🙋‍♂️ 🙍‍♀️ 🙍‍♂️ 🙎‍♀️ 🙎‍♂️ 🚣‍♀️ 🚣‍♂️ 🚴‍♀️ 🚴‍♂️ 🚵‍♀️ 🚵‍♂️ 🚶‍♀️ 🚶‍♂️ 🚶‍➡️ 🤦‍♀️ 🤦‍♂️ 🤵‍♀️ 🤵‍♂️ 🤷‍♀️ 🤷‍♂️ 🤸‍♀️ 🤸‍♂️ 🤹‍♀️ 🤹‍♂️ 🤼‍♀️ 🤼‍♂️ 🤽‍♀️ 🤽‍♂️ 🤾‍♀️ 🤾‍♂️ 🦸‍♀️ 🦸‍♂️ 🦹‍♀️ 🦹‍♂️ 🧍‍♀️ 🧍‍♂️ 🧎‍♀️ 🧎‍♂️ 🧎‍➡️ 🧏‍♀️ 🧏‍♂️ 🧑‍⚕️ 🧑‍⚖️ 🧑‍✈️ 🧑🏻‍🌾 🧑🏻‍🍳 🧑🏻‍🍼 🧑🏻‍🎄 🧑🏻‍🎤 🧑🏻‍🎨 🧑🏻‍🏫 🧑🏻‍💼 🧑🏻‍🔧 🧑🏻‍🔬 🧑🏻‍🚀 🧑🏻‍🚒 🧑🏻‍🦯 🧑🏻‍🦰 🧑🏻‍🦱 🧑🏻‍🦲 🧑🏻‍🦳 🧑🏻‍🦼 🧑🏻‍🦽 🧑🏼‍🌾 🧑🏼‍🍳 🧑🏼‍🍼 🧑🏼‍🎄 🧑🏼‍🎤 🧑🏼‍🎨 🧑🏼‍🏫 🧑🏼‍💼 🧑🏼‍🔧 🧑🏼‍🔬 🧑🏼‍🚀 🧑🏼‍🚒 🧑🏼‍🦯 🧑🏼‍🦰 🧑🏼‍🦱 🧑🏼‍🦲 🧑🏼‍🦳 🧑🏼‍🦼 🧑🏼‍🦽 🧑🏽‍🌾 🧑🏽‍🍳 🧑🏽‍🍼 🧑🏽‍🎄 🧑🏽‍🎤 🧑🏽‍🎨 🧑🏽‍🏫 🧑🏽‍💼 🧑🏽‍🔧 🧑🏽‍🔬 🧑🏽‍🚀 🧑🏽‍🚒 🧑🏽‍🦯 🧑🏽‍🦰 🧑🏽‍🦱 🧑🏽‍🦲 🧑🏽‍🦳 🧑🏽‍🦼 🧑🏽‍🦽 🧑🏾‍🌾 🧑🏾‍🍳 🧑🏾‍🍼 🧑🏾‍🎄 🧑🏾‍🎤 🧑🏾‍🎨 🧑🏾‍🏫 🧑🏾‍💼 🧑🏾‍🔧 🧑🏾‍🔬 🧑🏾‍🚀 🧑🏾‍🚒 🧑🏾‍🦯 🧑🏾‍🦰 🧑🏾‍🦱 🧑🏾‍🦲 🧑🏾‍🦳 🧑🏾‍🦼 🧑🏾‍🦽 🧑🏿‍🌾 🧑🏿‍🍳 🧑🏿‍🍼 🧑🏿‍🎄 🧑🏿‍🎤 🧑🏿‍🎨 🧑🏿‍🏫 🧑🏿‍💼 🧑🏿‍🔧 🧑🏿‍🔬 🧑🏿‍🚀 🧑🏿‍🚒 🧑🏿‍🦯 🧑🏿‍🦰 🧑🏿‍🦱 🧑🏿‍🦲 🧑🏿‍🦳 🧑🏿‍🦼 🧑🏿‍🦽 🧔‍♀️ 🧔‍♂️ 🧖‍♀️ 🧖‍♂️ 🧗‍♀️ 🧗‍♂️ 🧘‍♀️ 🧘‍♂️ 🧙‍♀️ 🧙‍♂️ 🧚‍♀️ 🧚‍♂️ 🧛‍♀️ 🧛‍♂️ 🧜‍♀️ 🧜‍♂️ 🧝‍♀️ 🧝‍♂️ 🧞‍♀️ 🧞‍♂️ 🧟‍♀️ 🧟‍♂️" .
        " 🐕️‍🦺 🐦️‍🔥 👨‍🎓️ 👨‍🏭️ 👨‍💻️ 👩‍🎓️ 👩‍🏭️ 👩‍💻️ 🧑‍🎓️ 🧑‍🏭️ 🧑‍💻️" .
        // 3 characters
        " #️⃣ *️⃣ 0️⃣ 1️⃣ 2️⃣ 3️⃣ 4️⃣ 5️⃣ 6️⃣ 7️⃣ 8️⃣ 9️⃣ 🍄‍🟫 🍋‍🟩 👨‍🌾 👨‍🍳 👨‍🍼 👨‍🎤 👨‍🎨 👨‍🏫 👨‍👦 👨‍👧 👨‍💼 👨‍🔧 👨‍🔬 👨‍🚀 👨‍🚒 👨‍🦯 👨‍🦰 👨‍🦱 👨‍🦲 👨‍🦳 👨‍🦼 👨‍🦽 👩‍🌾 👩‍🍳 👩‍🍼 👩‍🎤 👩‍🎨 👩‍🏫 👩‍👦 👩‍👧 👩‍💼 👩‍🔧 👩‍🔬 👩‍🚀 👩‍🚒 👩‍🦯 👩‍🦰 👩‍🦱 👩‍🦲 👩‍🦳 👩‍🦼 👩‍🦽 😮‍💨 😵‍💫 🧑‍🌾 🧑‍🍳 🧑‍🍼 🧑‍🎄 🧑‍🎤 🧑‍🎨 🧑‍🏫 🧑‍💼 🧑‍🔧 🧑‍🔬 🧑‍🚀 🧑‍🚒 🧑‍🦯 🧑‍🦰 🧑‍🦱 🧑‍🦲 🧑‍🦳 🧑‍🦼 🧑‍🦽 🧑‍🧒" .
        // 2 characters
        " ©️ ®️ ‼️ ⁉️ ™️ ℹ️ ↔️ ↕️ ↖️ ↗️ ↘️ ↙️ ↩️ ↪️ ⌨️ ⏏️ ⏭️ ⏮️ ⏯️ ⏱️ ⏲️ ⏸️ ⏹️ ⏺️ Ⓜ️ ▪️ ▫️ ▶️ ◀️ ◻️ ◼️ ☀️ ☁️ ☂️ ☃️ ☄️ ☎️ ☑️ ☘️ ☝️ ☝🏻 ☝🏼 ☝🏽 ☝🏾 ☝🏿 ☠️ ☢️ ☣️ ☦️ ☪️ ☮️ ☯️ ☸️ ☹️ ☺️ ♀️ ♂️ ♟️ ♠️ ♣️ ♥️ ♦️ ♨️ ♻️ ♾️ ⚒️ ⚔️ ⚕️ ⚖️ ⚗️ ⚙️ ⚛️ ⚜️ ⚠️ ⚧️ ⚰️ ⚱️ ⛈️ ⛏️ ⛑️ ⛓️ ⛩️ ⛰️ ⛱️ ⛴️ ⛷️ ⛸️ ⛹️ ⛹🏻 ⛹🏼 ⛹🏽 ⛹🏾 ⛹🏿 ✂️ ✈️ ✉️ ✊🏻 ✊🏼 ✊🏽 ✊🏾 ✊🏿 ✋🏻 ✋🏼 ✋🏽 ✋🏾 ✋🏿 ✌️ ✌🏻 ✌🏼 ✌🏽 ✌🏾 ✌🏿 ✍️ ✍🏻 ✍🏼 ✍🏽 ✍🏾 ✍🏿 ✏️ ✒️ ✔️ ✖️ ✝️ ✡️ ✳️ ✴️ ❄️ ❇️ ❣️ ❤️ ➡️ ⤴️ ⤵️ ⬅️ ⬆️ ⬇️ 〰️ 〽️ ㊗️ ㊙️ 🅰️ 🅱️ 🅾️ 🅿️ 🇦🇨 🇦🇩 🇦🇪 🇦🇫 🇦🇬 🇦🇮 🇦🇱 🇦🇲 🇦🇴 🇦🇶 🇦🇷 🇦🇸 🇦🇹 🇦🇺 🇦🇼 🇦🇽 🇦🇿 🇧🇦 🇧🇧 🇧🇩 🇧🇪 🇧🇫 🇧🇬 🇧🇭 🇧🇮 🇧🇯 🇧🇱 🇧🇲 🇧🇳 🇧🇴 🇧🇶 🇧🇷 🇧🇸 🇧🇹 🇧🇻 🇧🇼 🇧🇾 🇧🇿 🇨🇦 🇨🇨 🇨🇩 🇨🇫 🇨🇬 🇨🇭 🇨🇮 🇨🇰 🇨🇱 🇨🇲 🇨🇳 🇨🇴 🇨🇵 🇨🇶 🇨🇷 🇨🇺 🇨🇻 🇨🇼 🇨🇽 🇨🇾 🇨🇿 🇩🇪 🇩🇬 🇩🇯 🇩🇰 🇩🇲 🇩🇴 🇩🇿 🇪🇦 🇪🇨 🇪🇪 🇪🇬 🇪🇭 🇪🇷 🇪🇸 🇪🇹 🇪🇺 🇫🇮 🇫🇯 🇫🇰 🇫🇲 🇫🇴 🇫🇷 🇬🇦 🇬🇧 🇬🇩 🇬🇪 🇬🇫 🇬🇬 🇬🇭 🇬🇮 🇬🇱 🇬🇲 🇬🇳 🇬🇵 🇬🇶 🇬🇷 🇬🇸 🇬🇹 🇬🇺 🇬🇼 🇬🇾 🇭🇰 🇭🇲 🇭🇳 🇭🇷 🇭🇹 🇭🇺 🇮🇨 🇮🇩 🇮🇪 🇮🇱 🇮🇲 🇮🇳 🇮🇴 🇮🇶 🇮🇷 🇮🇸 🇮🇹 🇯🇪 🇯🇲 🇯🇴 🇯🇵 🇰🇪 🇰🇬 🇰🇭 🇰🇮 🇰🇲 🇰🇳 🇰🇵 🇰🇷 🇰🇼 🇰🇾 🇰🇿 🇱🇦 🇱🇧 🇱🇨 🇱🇮 🇱🇰 🇱🇷 🇱🇸 🇱🇹 🇱🇺 🇱🇻 🇱🇾 🇲🇦 🇲🇨 🇲🇩 🇲🇪 🇲🇫 🇲🇬 🇲🇭 🇲🇰 🇲🇱 🇲🇲 🇲🇳 🇲🇴 🇲🇵 🇲🇶 🇲🇷 🇲🇸 🇲🇹 🇲🇺 🇲🇻 🇲🇼 🇲🇽 🇲🇾 🇲🇿 🇳🇦 🇳🇨 🇳🇪 🇳🇫 🇳🇬 🇳🇮 🇳🇱 🇳🇴 🇳🇵 🇳🇷 🇳🇺 🇳🇿 🇴🇲 🇵🇦 🇵🇪 🇵🇫 🇵🇬 🇵🇭 🇵🇰 🇵🇱 🇵🇲 🇵🇳 🇵🇷 🇵🇸 🇵🇹 🇵🇼 🇵🇾 🇶🇦 🇷🇪 🇷🇴 🇷🇸 🇷🇺 🇷🇼 🇸🇦 🇸🇧 🇸🇨 🇸🇩 🇸🇪 🇸🇬 🇸🇭 🇸🇮 🇸🇯 🇸🇰 🇸🇱 🇸🇲 🇸🇳 🇸🇴 🇸🇷 🇸🇸 🇸🇹 🇸🇻 🇸🇽 🇸🇾 🇸🇿 🇹🇦 🇹🇨 🇹🇩 🇹🇫 🇹🇬 🇹🇭 🇹🇯 🇹🇰 🇹🇱 🇹🇲 🇹🇳 🇹🇴 🇹🇷 🇹🇹 🇹🇻 🇹🇼 🇹🇿 🇺🇦 🇺🇬 🇺🇲 🇺🇳 🇺🇸 🇺🇾 🇺🇿 🇻🇦 🇻🇨 🇻🇪 🇻🇬 🇻🇮 🇻🇳 🇻🇺 🇼🇫 🇼🇸 🇽🇰 🇾🇪 🇾🇹 🇿🇦 🇿🇲 🇿🇼 🈂️ 🈷️ 🌡️ 🌤️ 🌥️ 🌦️ 🌧️ 🌨️ 🌩️ 🌪️ 🌫️ 🌬️ 🌶️ 🍽️ 🎅🏻 🎅🏼 🎅🏽 🎅🏾 🎅🏿 🎖️ 🎗️ 🎙️ 🎚️ 🎛️ 🎞️ 🎟️ 🏂🏻 🏂🏼 🏂🏽 🏂🏾 🏂🏿 🏃🏻 🏃🏼 🏃🏽 🏃🏾 🏃🏿 🏄🏻 🏄🏼 🏄🏽 🏄🏾 🏄🏿 🏇🏻 🏇🏼 🏇🏽 🏇🏾 🏇🏿 🏊🏻 🏊🏼 🏊🏽 🏊🏾 🏊🏿 🏋️ 🏋🏻 🏋🏼 🏋🏽 🏋🏾 🏋🏿 🏌️ 🏌🏻 🏌🏼 🏌🏽 🏌🏾 🏌🏿 🏍️ 🏎️ 🏔️ 🏕️ 🏖️ 🏗️ 🏘️ 🏙️ 🏚️ 🏛️ 🏜️ 🏝️ 🏞️ 🏟️ 🏳️ 🏵️ 🏷️ 🐿️ 👁️ 👂🏻 👂🏼 👂🏽 👂🏾 👂🏿 👃🏻 👃🏼 👃🏽 👃🏾 👃🏿 👆🏻 👆🏼 👆🏽 👆🏾 👆🏿 👇🏻 👇🏼 👇🏽 👇🏾 👇🏿 👈🏻 👈🏼 👈🏽 👈🏾 👈🏿 👉🏻 👉🏼 👉🏽 👉🏾 👉🏿 👊🏻 👊🏼 👊🏽 👊🏾 👊🏿 👋🏻 👋🏼 👋🏽 👋🏾 👋🏿 👌🏻 👌🏼 👌🏽 👌🏾 👌🏿 👍🏻 👍🏼 👍🏽 👍🏾 👍🏿 👎🏻 👎🏼 👎🏽 👎🏾 👎🏿 👏🏻 👏🏼 👏🏽 👏🏾 👏🏿 👐🏻 👐🏼 👐🏽 👐🏾 👐🏿 👦🏻 👦🏼 👦🏽 👦🏾 👦🏿 👧🏻 👧🏼 👧🏽 👧🏾 👧🏿 👨🏻 👨🏼 👨🏽 👨🏾 👨🏿 👩🏻 👩🏼 👩🏽 👩🏾 👩🏿 👫🏻 👫🏼 👫🏽 👫🏾 👫🏿 👬🏻 👬🏼 👬🏽 👬🏾 👬🏿 👭🏻 👭🏼 👭🏽 👭🏾 👭🏿 👮🏻 👮🏼 👮🏽 👮🏾 👮🏿 👰🏻 👰🏼 👰🏽 👰🏾 👰🏿 👱🏻 👱🏼 👱🏽 👱🏾 👱🏿 👲🏻 👲🏼 👲🏽 👲🏾 👲🏿 👳🏻 👳🏼 👳🏽 👳🏾 👳🏿 👴🏻 👴🏼 👴🏽 👴🏾 👴🏿 👵🏻 👵🏼 👵🏽 👵🏾 👵🏿 👶🏻 👶🏼 👶🏽 👶🏾 👶🏿 👷🏻 👷🏼 👷🏽 👷🏾 👷🏿 👸🏻 👸🏼 👸🏽 👸🏾 👸🏿 👼🏻 👼🏼 👼🏽 👼🏾 👼🏿 💁🏻 💁🏼 💁🏽 💁🏾 💁🏿 💂🏻 💂🏼 💂🏽 💂🏾 💂🏿 💃🏻 💃🏼 💃🏽 💃🏾 💃🏿 💅🏻 💅🏼 💅🏽 💅🏾 💅🏿 💆🏻 💆🏼 💆🏽 💆🏾 💆🏿 💇🏻 💇🏼 💇🏽 💇🏾 💇🏿 💏🏻 💏🏼 💏🏽 💏🏾 💏🏿 💑🏻 💑🏼 💑🏽 💑🏾 💑🏿 💪🏻 💪🏼 💪🏽 💪🏾 💪🏿 📽️ 🕉️ 🕊️ 🕯️ 🕰️ 🕳️ 🕴️ 🕴🏻 🕴🏼 🕴🏽 🕴🏾 🕴🏿 🕵️ 🕵🏻 🕵🏼 🕵🏽 🕵🏾 🕵🏿 🕶️ 🕷️ 🕸️ 🕹️ 🕺🏻 🕺🏼 🕺🏽 🕺🏾 🕺🏿 🖇️ 🖊️ 🖋️ 🖌️ 🖍️ 🖐️ 🖐🏻 🖐🏼 🖐🏽 🖐🏾 🖐🏿 🖕🏻 🖕🏼 🖕🏽 🖕🏾 🖕🏿 🖖🏻 🖖🏼 🖖🏽 🖖🏾 🖖🏿 🖥️ 🖨️ 🖱️ 🖲️ 🖼️ 🗂️ 🗃️ 🗄️ 🗑️ 🗒️ 🗓️ 🗜️ 🗝️ 🗞️ 🗡️ 🗣️ 🗨️ 🗯️ 🗳️ 🗺️ 🙅🏻 🙅🏼 🙅🏽 🙅🏾 🙅🏿 🙆🏻 🙆🏼 🙆🏽 🙆🏾 🙆🏿 🙇🏻 🙇🏼 🙇🏽 🙇🏾 🙇🏿 🙋🏻 🙋🏼 🙋🏽 🙋🏾 🙋🏿 🙌🏻 🙌🏼 🙌🏽 🙌🏾 🙌🏿 🙍🏻 🙍🏼 🙍🏽 🙍🏾 🙍🏿 🙎🏻 🙎🏼 🙎🏽 🙎🏾 🙎🏿 🙏🏻 🙏🏼 🙏🏽 🙏🏾 🙏🏿 🚣🏻 🚣🏼 🚣🏽 🚣🏾 🚣🏿 🚴🏻 🚴🏼 🚴🏽 🚴🏾 🚴🏿 🚵🏻 🚵🏼 🚵🏽 🚵🏾 🚵🏿 🚶🏻 🚶🏼 🚶🏽 🚶🏾 🚶🏿 🛀🏻 🛀🏼 🛀🏽 🛀🏾 🛀🏿 🛋️ 🛌🏻 🛌🏼 🛌🏽 🛌🏾 🛌🏿 🛍️ 🛎️ 🛏️ 🛠️ 🛡️ 🛢️ 🛣️ 🛤️ 🛥️ 🛩️ 🛰️ 🛳️ 🤌🏻 🤌🏼 🤌🏽 🤌🏾 🤌🏿 🤏🏻 🤏🏼 🤏🏽 🤏🏾 🤏🏿 🤘🏻 🤘🏼 🤘🏽 🤘🏾 🤘🏿 🤙🏻 🤙🏼 🤙🏽 🤙🏾 🤙🏿 🤚🏻 🤚🏼 🤚🏽 🤚🏾 🤚🏿 🤛🏻 🤛🏼 🤛🏽 🤛🏾 🤛🏿 🤜🏻 🤜🏼 🤜🏽 🤜🏾 🤜🏿 🤝🏻 🤝🏼 🤝🏽 🤝🏾 🤝🏿 🤞🏻 🤞🏼 🤞🏽 🤞🏾 🤞🏿 🤟🏻 🤟🏼 🤟🏽 🤟🏾 🤟🏿 🤦🏻 🤦🏼 🤦🏽 🤦🏾 🤦🏿 🤰🏻 🤰🏼 🤰🏽 🤰🏾 🤰🏿 🤱🏻 🤱🏼 🤱🏽 🤱🏾 🤱🏿 🤲🏻 🤲🏼 🤲🏽 🤲🏾 🤲🏿 🤳🏻 🤳🏼 🤳🏽 🤳🏾 🤳🏿 🤴🏻 🤴🏼 🤴🏽 🤴🏾 🤴🏿 🤵🏻 🤵🏼 🤵🏽 🤵🏾 🤵🏿 🤶🏻 🤶🏼 🤶🏽 🤶🏾 🤶🏿 🤷🏻 🤷🏼 🤷🏽 🤷🏾 🤷🏿 🤸🏻 🤸🏼 🤸🏽 🤸🏾 🤸🏿 🤹🏻 🤹🏼 🤹🏽 🤹🏾 🤹🏿 🤽🏻 🤽🏼 🤽🏽 🤽🏾 🤽🏿 🤾🏻 🤾🏼 🤾🏽 🤾🏾 🤾🏿 🥷🏻 🥷🏼 🥷🏽 🥷🏾 🥷🏿 🦵🏻 🦵🏼 🦵🏽 🦵🏾 🦵🏿 🦶🏻 🦶🏼 🦶🏽 🦶🏾 🦶🏿 🦸🏻 🦸🏼 🦸🏽 🦸🏾 🦸🏿 🦹🏻 🦹🏼 🦹🏽 🦹🏾 🦹🏿 🦻🏻 🦻🏼 🦻🏽 🦻🏾 🦻🏿 🧍🏻 🧍🏼 🧍🏽 🧍🏾 🧍🏿 🧎🏻 🧎🏼 🧎🏽 🧎🏾 🧎🏿 🧏🏻 🧏🏼 🧏🏽 🧏🏾 🧏🏿 🧑🏻 🧑🏼 🧑🏽 🧑🏾 🧑🏿 🧒🏻 🧒🏼 🧒🏽 🧒🏾 🧒🏿 🧓🏻 🧓🏼 🧓🏽 🧓🏾 🧓🏿 🧔🏻 🧔🏼 🧔🏽 🧔🏾 🧔🏿 🧕🏻 🧕🏼 🧕🏽 🧕🏾 🧕🏿 🧖🏻 🧖🏼 🧖🏽 🧖🏾 🧖🏿 🧗🏻 🧗🏼 🧗🏽 🧗🏾 🧗🏿 🧘🏻 🧘🏼 🧘🏽 🧘🏾 🧘🏿 🧙🏻 🧙🏼 🧙🏽 🧙🏾 🧙🏿 🧚🏻 🧚🏼 🧚🏽 🧚🏾 🧚🏿 🧛🏻 🧛🏼 🧛🏽 🧛🏾 🧛🏿 🧜🏻 🧜🏼 🧜🏽 🧜🏾 🧜🏿 🧝🏻 🧝🏼 🧝🏽 🧝🏾 🧝🏿 🫃🏻 🫃🏼 🫃🏽 🫃🏾 🫃🏿 🫄🏻 🫄🏼 🫄🏽 🫄🏾 🫄🏿 🫅🏻 🫅🏼 🫅🏽 🫅🏾 🫅🏿 🫰🏻 🫰🏼 🫰🏽 🫰🏾 🫰🏿 🫱🏻 🫱🏼 🫱🏽 🫱🏾 🫱🏿 🫲🏻 🫲🏼 🫲🏽 🫲🏾 🫲🏿 🫳🏻 🫳🏼 🫳🏽 🫳🏾 🫳🏿 🫴🏻 🫴🏼 🫴🏽 🫴🏾 🫴🏿 🫵🏻 🫵🏼 🫵🏽 🫵🏾 🫵🏿 🫶🏻 🫶🏼 🫶🏽 🫶🏾 🫶🏿 🫷🏻 🫷🏼 🫷🏽 🫷🏾 🫷🏿 🫸🏻 🫸🏼 🫸🏽 🫸🏾 🫸🏿" .
        " ⌚️ ⌛️ ⏩️ ⏪️ ⏫️ ⏬️ ⏰️ ⏳️ ◽️ ◾️ ☔️ ☕️ ♈️ ♉️ ♊️ ♋️ ♌️ ♍️ ♎️ ♏️ ♐️ ♑️ ♒️ ♓️ ♿️ ⚓️ ⚡️ ⚪️ ⚫️ ⚽️ ⚾️ ⛄️ ⛅️ ⛎️ ⛔️ ⛪️ ⛲️ ⛳️ ⛵️ ⛺️ ⛽️ ✅️ ✊️ ✋️ ✨️ ❌️ ❎️ ❓️ ❔️ ❕️ ❗️ ➕️ ➖️ ➗️ ➰️ ➿️ ⬛️ ⬜️ ⭐️ ⭕️ 🀄️ 🈚️ 🈯️ 🌍️ 🌎️ 🌏️ 🌕️ 🌜️ 🍸️ 🎓️ 🎧️ 🎬️ 🎭️ 🎮️ 🏂️ 🏄️ 🏆️ 🏊️ 🏠️ 🏭️ 🐈️ 🐕️ 🐟️ 🐦️ 👂️ 👆️ 👇️ 👈️ 👉️ 👍️ 👎️ 👓️ 👪️ 👽️ 💣️ 💰️ 💳️ 💻️ 💿️ 📋️ 📚️ 📟️ 📤️ 📥️ 📦️ 📪️ 📫️ 📬️ 📭️ 📷️ 📹️ 📺️ 📻️ 🔈️ 🔍️ 🔒️ 🔓️ 🕐️ 🕑️ 🕒️ 🕓️ 🕔️ 🕕️ 🕖️ 🕗️ 🕘️ 🕙️ 🕚️ 🕛️ 🕜️ 🕝️ 🕞️ 🕟️ 🕠️ 🕡️ 🕢️ 🕣️ 🕤️ 🕥️ 🕦️ 🕧️ 😐️ 🚇️ 🚍️ 🚑️ 🚔️ 🚘️ 🚭️ 🚲️ 🚹️ 🚺️ 🚼️" .
        // 1 character
        " 🃏 🆎 🆑 🆒 🆓 🆔 🆕 🆖 🆗 🆘 🆙 🆚 🈁 🈲 🈳 🈴 🈵 🈶 🈸 🈹 🈺 🉐 🉑 🌀 🌁 🌂 🌃 🌄 🌅 🌆 🌇 🌈 🌉 🌊 🌋 🌌 🌐 🌑 🌒 🌓 🌔 🌖 🌗 🌘 🌙 🌚 🌛 🌝 🌞 🌟 🌠 🌭 🌮 🌯 🌰 🌱 🌲 🌳 🌴 🌵 🌷 🌸 🌹 🌺 🌻 🌼 🌽 🌾 🌿 🍀 🍁 🍂 🍃 🍄 🍅 🍆 🍇 🍈 🍉 🍊 🍋 🍌 🍍 🍎 🍏 🍐 🍑 🍒 🍓 🍔 🍕 🍖 🍗 🍘 🍙 🍚 🍛 🍜 🍝 🍞 🍟 🍠 🍡 🍢 🍣 🍤 🍥 🍦 🍧 🍨 🍩 🍪 🍫 🍬 🍭 🍮 🍯 🍰 🍱 🍲 🍳 🍴 🍵 🍶 🍷 🍹 🍺 🍻 🍼 🍾 🍿 🎀 🎁 🎂 🎃 🎄 🎅 🎆 🎇 🎈 🎉 🎊 🎋 🎌 🎍 🎎 🎏 🎐 🎑 🎒 🎠 🎡 🎢 🎣 🎤 🎥 🎦 🎨 🎩 🎪 🎫 🎯 🎰 🎱 🎲 🎳 🎴 🎵 🎶 🎷 🎸 🎹 🎺 🎻 🎼 🎽 🎾 🎿 🏀 🏁 🏃 🏅 🏇 🏈 🏉 🏏 🏐 🏑 🏒 🏓 🏡 🏢 🏣 🏤 🏥 🏦 🏧 🏨 🏩 🏪 🏫 🏬 🏮 🏯 🏰 🏴 🏸 🏹 🏺 🏻 🏼 🏽 🏾 🏿 🐀 🐁 🐂 🐃 🐄 🐅 🐆 🐇 🐉 🐊 🐋 🐌 🐍 🐎 🐏 🐐 🐑 🐒 🐓 🐔 🐖 🐗 🐘 🐙 🐚 🐛 🐜 🐝 🐞 🐠 🐡 🐢 🐣 🐤 🐥 🐧 🐨 🐩 🐪 🐫 🐬 🐭 🐮 🐯 🐰 🐱 🐲 🐳 🐴 🐵 🐶 🐷 🐸 🐹 🐺 🐻 🐼 🐽 🐾 👀 👃 👄 👅 👊 👋 👌 👏 👐 👑 👒 👔 👕 👖 👗 👘 👙 👚 👛 👜 👝 👞 👟 👠 👡 👢 👣 👤 👥 👦 👧 👨 👩 👫 👬 👭 👮 👯 👰 👱 👲 👳 👴 👵 👶 👷 👸 👹 👺 👻 👼 👾 👿 💀 💁 💂 💃 💄 💅 💆 💇 💈 💉 💊 💋 💌 💍 💎 💏 💐 💑 💒 💓 💔 💕 💖 💗 💘 💙 💚 💛 💜 💝 💞 💟 💠 💡 💢 💤 💥 💦 💧 💨 💩 💪 💫 💬 💭 💮 💯 💱 💲 💴 💵 💶 💷 💸 💹 💺 💼 💽 💾 📀 📁 📂 📃 📄 📅 📆 📇 📈 📉 📊 📌 📍 📎 📏 📐 📑 📒 📓 📔 📕 📖 📗 📘 📙 📛 📜 📝 📞 📠 📡 📢 📣 📧 📨 📩 📮 📯 📰 📱 📲 📳 📴 📵 📶 📸 📼 📿 🔀 🔁 🔂 🔃 🔄 🔅 🔆 🔇 🔉 🔊 🔋 🔌 🔎 🔏 🔐 🔑 🔔 🔕 🔖 🔗 🔘 🔙 🔚 🔛 🔜 🔝 🔞 🔟 🔠 🔡 🔢 🔣 🔤 🔥 🔦 🔧 🔨 🔩 🔪 🔫 🔬 🔭 🔮 🔯 🔰 🔱 🔲 🔳 🔴 🔵 🔶 🔷 🔸 🔹 🔺 🔻 🔼 🔽 🕋 🕌 🕍 🕎 🕺 🖕 🖖 🖤 🗻 🗼 🗽 🗾 🗿 😀 😁 😂 😃 😄 😅 😆 😇 😈 😉 😊 😋 😌 😍 😎 😏 😑 😒 😓 😔 😕 😖 😗 😘 😙 😚 😛 😜 😝 😞 😟 😠 😡 😢 😣 😤 😥 😦 😧 😨 😩 😪 😫 😬 😭 😮 😯 😰 😱 😲 😳 😴 😵 😶 😷 😸 😹 😺 😻 😼 😽 😾 😿 🙀 🙁 🙂 🙃 🙄 🙅 🙆 🙇 🙈 🙉 🙊 🙋 🙌 🙍 🙎 🙏 🚀 🚁 🚂 🚃 🚄 🚅 🚆 🚈 🚉 🚊 🚋 🚌 🚎 🚏 🚐 🚒 🚓 🚕 🚖 🚗 🚙 🚚 🚛 🚜 🚝 🚞 🚟 🚠 🚡 🚢 🚣 🚤 🚥 🚦 🚧 🚨 🚩 🚪 🚫 🚬 🚮 🚯 🚰 🚱 🚳 🚴 🚵 🚶 🚷 🚸 🚻 🚽 🚾 🚿 🛀 🛁 🛂 🛃 🛄 🛅 🛌 🛐 🛑 🛒 🛕 🛖 🛗 🛜 🛝 🛞 🛟 🛫 🛬 🛴 🛵 🛶 🛷 🛸 🛹 🛺 🛻 🛼 🟠 🟡 🟢 🟣 🟤 🟥 🟦 🟧 🟨 🟩 🟪 🟫 🟰 🤌 🤍 🤎 🤏 🤐 🤑 🤒 🤓 🤔 🤕 🤖 🤗 🤘 🤙 🤚 🤛 🤜 🤝 🤞 🤟 🤠 🤡 🤢 🤣 🤤 🤥 🤦 🤧 🤨 🤩 🤪 🤫 🤬 🤭 🤮 🤯 🤰 🤱 🤲 🤳 🤴 🤵 🤶 🤷 🤸 🤹 🤺 🤼 🤽 🤾 🤿 🥀 🥁 🥂 🥃 🥄 🥅 🥇 🥈 🥉 🥊 🥋 🥌 🥍 🥎 🥏 🥐 🥑 🥒 🥓 🥔 🥕 🥖 🥗 🥘 🥙 🥚 🥛 🥜 🥝 🥞 🥟 🥠 🥡 🥢 🥣 🥤 🥥 🥦 🥧 🥨 🥩 🥪 🥫 🥬 🥭 🥮 🥯 🥰 🥱 🥲 🥳 🥴 🥵 🥶 🥷 🥸 🥹 🥺 🥻 🥼 🥽 🥾 🥿 🦀 🦁 🦂 🦃 🦄 🦅 🦆 🦇 🦈 🦉 🦊 🦋 🦌 🦍 🦎 🦏 🦐 🦑 🦒 🦓 🦔 🦕 🦖 🦗 🦘 🦙 🦚 🦛 🦜 🦝 🦞 🦟 🦠 🦡 🦢 🦣 🦤 🦥 🦦 🦧 🦨 🦩 🦪 🦫 🦬 🦭 🦮 🦯 🦰 🦱 🦲 🦳 🦴 🦵 🦶 🦷 🦸 🦹 🦺 🦻 🦼 🦽 🦾 🦿 🧀 🧁 🧂 🧃 🧄 🧅 🧆 🧇 🧈 🧉 🧊 🧋 🧌 🧍 🧎 🧏 🧐 🧑 🧒 🧓 🧔 🧕 🧖 🧗 🧘 🧙 🧚 🧛 🧜 🧝 🧞 🧟 🧠 🧡 🧢 🧣 🧤 🧥 🧦 🧧 🧨 🧩 🧪 🧫 🧬 🧭 🧮 🧯 🧰 🧱 🧲 🧳 🧴 🧵 🧶 🧷 🧸 🧹 🧺 🧻 🧼 🧽 🧾 🧿 🩰 🩱 🩲 🩳 🩴 🩵 🩶 🩷 🩸 🩹 🩺 🩻 🩼 🪀 🪁 🪂 🪃 🪄 🪅 🪆 🪇 🪈 🪉 🪏 🪐 🪑 🪒 🪓 🪔 🪕 🪖 🪗 🪘 🪙 🪚 🪛 🪜 🪝 🪞 🪟 🪠 🪡 🪢 🪣 🪤 🪥 🪦 🪧 🪨 🪩 🪪 🪫 🪬 🪭 🪮 🪯 🪰 🪱 🪲 🪳 🪴 🪵 🪶 🪷 🪸 🪹 🪺 🪻 🪼 🪽 🪾 🪿 🫀 🫁 🫂 🫃 🫄 🫅 🫆 🫎 🫏 🫐 🫑 🫒 🫓 🫔 🫕 🫖 🫗 🫘 🫙 🫚 🫛 🫜 🫟 🫠 🫡 🫢 🫣 🫤 🫥 🫦 🫧 🫨 🫩 🫰 🫱 🫲 🫳 🫴 🫵 🫶 🫷 🫸"
    ;

    /**
     * Replace emoji characters in content with corresponding <img> tags.
     *
     * @param string $content
     * @return string
     */
    public function replace_emoji_with_images(string $content): string {
        global $CFG;

        foreach (explode(' ', $this->allemojis) as $emoji) {
            $unicodepoints = [];
            // Split emoji string into its Unicode code points.
            $characters = preg_split('//u', $emoji, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($characters as $char) {
                $unicodepoints[] = sprintf("%04x", mb_ord($char, 'UTF-8'));
            }
            $unicode = implode('_', $unicodepoints);
            $file = $CFG->dirroot . '/local/course_exporter/pix/emoji/emoji_u' . $unicode . '.svg';
            $image = '';
            if (file_exists($file)) {
                $src = $CFG->wwwroot . '/local/course_exporter/pix/emoji/emoji_u' . $unicode . '.svg';
                $attrs = [
                    'width' => '1.35em',
                    'height' => '1.35em',
                ];
                $image = \html_writer::img($src, '', $attrs);
            }
            // Replace emoji with image.
            // Keep white space before emoji or add an invisible character.
            $content = str_replace(' ' . $emoji, ' ' . $image, $content);
            $content = str_replace($emoji, '&#12288;' . $image, $content);
        }
        return $content;
    }

    /**
     * Replace math characters in content with corresponding TeX tags.
     * @param string $content
     * @return string
     */
    public function replace_math_characters_with_tex(string $content): string {
        foreach ($this->mathcharacters as $character => $tex) {
            $content = str_replace($character, $tex, $content);
        }

        return $content;
    }

    /**
     * Recursively convert relative CSS units (rem, em) to pixels in a DOM node.
     *
     * @param \DOMNode $node
     * @param int $rootsize Base size for rem conversion.
     * @param int $fontsize Base size for em conversion.
     */
    public function fix_relative_units(\DOMNode &$node, int $rootsize, int $fontsize): void {
        if ($node->hasAttributes()) {
            // Attributes to match.
            $attributes = ['style', 'height', 'width'];
            foreach ($attributes as $attribute) {
                $item = $node->attributes->getNamedItem($attribute);
                if (!$item) {
                    continue;
                }
    
                $value = $item->nodeValue;
                // Fix rem units.
                $newvalue = preg_replace_callback('/(\d*\.?\d+)rem/', function ($matches) use ($rootsize) {
                    return ((int) $matches[1] * $rootsize) . 'px';
                }, $value);
                // Fix em units.
                $newvalue = preg_replace_callback('/(\d*\.?\d+)em/', function ($matches) use (&$fontsize) {
                    $fontsize = ((int) $matches[1] * $fontsize);
                    return $fontsize . 'px';
                }, $newvalue);
    
                if ($value !== $newvalue) {
                    $node->setAttribute($attribute, $newvalue);
                }
            }
        }
    
        // Recursively iterate through child nodes
        foreach ($node->childNodes as $child) {
            $this->fix_relative_units($child, $rootsize,$fontsize);
        }
    }
}
