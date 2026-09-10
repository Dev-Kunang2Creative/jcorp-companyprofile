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
                            Bagian ini disiapkan untuk profil direksi setelah
                            foto, nama, dan jabatan resminya diterima.
                        </p>
                    </div>

                    <div className="home-leadership-profile reveal">
                        <div
                            className="home-leadership-photo-placeholder"
                            role="img"
                            aria-label="Placeholder foto direksi J-Corporate"
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
                                <span>Nama direksi</span>
                                <span>Jabatan</span>
                                <span>Profil singkat</span>
                            </div>
                            <p className="home-leadership-status">
                                Materi resmi akan ditampilkan di sini setelah
                                dikirimkan oleh pihak J-Corporate.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
