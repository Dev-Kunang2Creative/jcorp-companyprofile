export default function HomeLeadership() {
    return (
        <section id="direksi" className="home-section home-leadership-section">
            <div className="home-mock-container">
                <div className="home-leadership-panel">
                    <div className="home-leadership-intro reveal">
                        <p className="home-mock-eyebrow">Direksi</p>
                        <h2>
                            Mengenal <em>J-Corporate</em>
                        </h2>
                        <p>
                            Dipimpin oleh sosok yang berpengalaman di bidang
                            pengembangan usaha dan manajemen grup.
                        </p>
                    </div>

                    <div className="home-leadership-profile reveal">
                        <div
                            className="home-leadership-photo-placeholder"
                            role="img"
                            aria-label="Foto Johan Setiadi Agung Nugroho — Direktur Utama J-Corporate"
                        >
                            <span
                                className="home-leadership-avatar"
                                aria-hidden="true"
                            />
                            <span
                                className="home-leadership-shoulders"
                                aria-hidden="true"
                            />
                            <span className="home-leadership-photo-label">
                                Foto direksi
                            </span>
                        </div>

                        <div className="home-leadership-details">
                            <p className="home-card-label">Profil direksi</p>
                            <div className="home-leadership-copy-placeholder">
                                <span>Johan Setiadi Agung Nugroho</span>
                                <span>Direktur Utama</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
