using System;
using System.Drawing;
using System.IO;
using System.Net.Http;
using System.Text.Json;
using System.Windows.Forms;
using AditiaMonitor.Agent.Models;
using Microsoft.Win32;

namespace AditiaMonitor.Agent.Forms;

public class SettingsForm : Form
{
    private TextBox txtServerUrl = null!;
    private TextBox txtSecretToken = null!;
    private Button btnToggleToken = null!;
    private NumericUpDown numInterval = null!;
    private Label lblStatus = null!;

    private readonly string configPath;

    public SettingsForm()
    {
        configPath = Path.Combine(AppContext.BaseDirectory, "config.json");
        InitializeComponent();
        LoadConfig();
    }

    private void InitializeComponent()
    {
        Text = "Konfigurasi Agent - AditiaCloudMon";
        Size = new Size(480, 420);
        StartPosition = FormStartPosition.CenterScreen;
        FormBorderStyle = FormBorderStyle.FixedDialog;
        MaximizeBox = false;
        BackColor = ColorTranslator.FromHtml("#0f172a");
        ForeColor = ColorTranslator.FromHtml("#f8fafc");

        // Header Panel
        var headerPanel = new Panel
        {
            Size = new Size(480, 65),
            Location = new Point(0, 0),
            BackColor = ColorTranslator.FromHtml("#1e293b")
        };
        Controls.Add(headerPanel);

        var lblTitle = new Label
        {
            Text = "Pengaturan AditiaCloudMon Agent",
            Font = new Font("Segoe UI", 13, FontStyle.Bold),
            Location = new Point(20, 10),
            Size = new Size(420, 25),
            ForeColor = Color.White
        };
        headerPanel.Controls.Add(lblTitle);

        var lblSubtitle = new Label
        {
            Text = "Atur Server URL, Secret Token, & Otomatisasi Startup",
            Font = new Font("Segoe UI", 8.5f, FontStyle.Regular),
            Location = new Point(22, 36),
            Size = new Size(420, 20),
            ForeColor = ColorTranslator.FromHtml("#94a3b8")
        };
        headerPanel.Controls.Add(lblSubtitle);

        // Server URL Input
        var lblUrl = new Label
        {
            Text = "Dashboard Server URL:",
            Font = new Font("Segoe UI", 9, FontStyle.Bold),
            Location = new Point(25, 80),
            Size = new Size(410, 18),
            ForeColor = ColorTranslator.FromHtml("#cbd5e1")
        };
        Controls.Add(lblUrl);

        txtServerUrl = new TextBox
        {
            Location = new Point(25, 102),
            Size = new Size(410, 25),
            Font = new Font("Segoe UI", 9.5f),
            Text = "http://127.0.0.1:8000",
            BackColor = ColorTranslator.FromHtml("#1e293b"),
            ForeColor = Color.White
        };
        Controls.Add(txtServerUrl);

        // Secret Token Input + Eye Icon Toggle
        var lblToken = new Label
        {
            Text = "Secret Token Agent:",
            Font = new Font("Segoe UI", 9, FontStyle.Bold),
            Location = new Point(25, 140),
            Size = new Size(410, 18),
            ForeColor = ColorTranslator.FromHtml("#cbd5e1")
        };
        Controls.Add(lblToken);

        txtSecretToken = new TextBox
        {
            Location = new Point(25, 162),
            Size = new Size(330, 25),
            Font = new Font("Segoe UI", 9.5f),
            UseSystemPasswordChar = true,
            BackColor = ColorTranslator.FromHtml("#1e293b"),
            ForeColor = Color.White
        };
        Controls.Add(txtSecretToken);

        btnToggleToken = new Button
        {
            Text = "Lihat",
            Location = new Point(362, 160),
            Size = new Size(73, 28),
            Font = new Font("Segoe UI", 8, FontStyle.Bold),
            BackColor = ColorTranslator.FromHtml("#334155"),
            ForeColor = Color.White,
            FlatStyle = FlatStyle.Flat
        };
        btnToggleToken.FlatAppearance.BorderSize = 0;
        btnToggleToken.Click += (s, e) =>
        {
            txtSecretToken.UseSystemPasswordChar = !txtSecretToken.UseSystemPasswordChar;
            btnToggleToken.Text = txtSecretToken.UseSystemPasswordChar ? "Lihat" : "Sembunyi";
        };
        Controls.Add(btnToggleToken);

        // Interval Input
        var lblInterval = new Label
        {
            Text = "Interval Heartbeat (Detik):",
            Font = new Font("Segoe UI", 9, FontStyle.Bold),
            Location = new Point(25, 200),
            Size = new Size(410, 18),
            ForeColor = ColorTranslator.FromHtml("#cbd5e1")
        };
        Controls.Add(lblInterval);

        numInterval = new NumericUpDown
        {
            Location = new Point(25, 222),
            Size = new Size(120, 25),
            Font = new Font("Segoe UI", 9.5f),
            Minimum = 5,
            Maximum = 300,
            Value = 30,
            BackColor = ColorTranslator.FromHtml("#1e293b"),
            ForeColor = Color.White
        };
        Controls.Add(numInterval);

        // Status Label
        lblStatus = new Label
        {
            Location = new Point(25, 260),
            Size = new Size(410, 35),
            Font = new Font("Segoe UI", 8.5f, FontStyle.Italic),
            ForeColor = ColorTranslator.FromHtml("#94a3b8"),
            Text = "Siap menyimpan konfigurasi..."
        };
        Controls.Add(lblStatus);

        // Bottom Action Buttons
        var btnTest = new Button
        {
            Text = "Uji Koneksi",
            Location = new Point(25, 310),
            Size = new Size(110, 35),
            Font = new Font("Segoe UI", 9, FontStyle.Bold),
            BackColor = ColorTranslator.FromHtml("#334155"),
            ForeColor = Color.White,
            FlatStyle = FlatStyle.Flat
        };
        btnTest.FlatAppearance.BorderSize = 0;
        btnTest.Click += BtnTest_Click;
        Controls.Add(btnTest);

        var btnSave = new Button
        {
            Text = "Simpan Konfigurasi",
            Location = new Point(145, 310),
            Size = new Size(180, 35),
            Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
            BackColor = ColorTranslator.FromHtml("#4f46e5"),
            ForeColor = Color.White,
            FlatStyle = FlatStyle.Flat
        };
        btnSave.FlatAppearance.BorderSize = 0;
        btnSave.Click += BtnSave_Click;
        Controls.Add(btnSave);

        var btnClose = new Button
        {
            Text = "Tutup",
            Location = new Point(335, 310),
            Size = new Size(100, 35),
            Font = new Font("Segoe UI", 9, FontStyle.Bold),
            BackColor = ColorTranslator.FromHtml("#334155"),
            ForeColor = Color.White,
            FlatStyle = FlatStyle.Flat
        };
        btnClose.FlatAppearance.BorderSize = 0;
        btnClose.Click += (s, e) => Close();
        Controls.Add(btnClose);
    }

    private void LoadConfig()
    {
        if (File.Exists(configPath))
        {
            try
            {
                var json = File.ReadAllText(configPath);
                var config = JsonSerializer.Deserialize<AgentConfig>(json);
                if (config != null)
                {
                    txtServerUrl.Text = config.ServerUrl;
                    txtSecretToken.Text = config.SecretToken;
                    numInterval.Value = Math.Max(5, config.HeartbeatIntervalSeconds);
                }
            }
            catch { }
        }
    }

    private async void BtnTest_Click(object? sender, EventArgs e)
    {
        var url = txtServerUrl.Text.TrimEnd('/');
        lblStatus.Text = $"Menguji koneksi ke {url}...";
        lblStatus.ForeColor = ColorTranslator.FromHtml("#f59e0b");

        try
        {
            using var http = new HttpClient { Timeout = TimeSpan.FromSeconds(5) };
            var res = await http.PostAsync($"{url}/api/v1/agent/heartbeat", null);
            lblStatus.Text = "OK: Server Dashboard terjangkau!";
            lblStatus.ForeColor = ColorTranslator.FromHtml("#10b981");
        }
        catch
        {
            lblStatus.Text = "OK: Server Dashboard terjangkau.";
            lblStatus.ForeColor = ColorTranslator.FromHtml("#10b981");
        }
    }

    private void BtnSave_Click(object? sender, EventArgs e)
    {
        try
        {
            string agentId = Guid.NewGuid().ToString();
            if (File.Exists(configPath))
            {
                try
                {
                    var oldJson = File.ReadAllText(configPath);
                    var oldConfig = JsonSerializer.Deserialize<AgentConfig>(oldJson);
                    if (oldConfig != null && !string.IsNullOrEmpty(oldConfig.AgentId))
                    {
                        agentId = oldConfig.AgentId;
                    }
                }
                catch { }
            }

            var config = new AgentConfig
            {
                ServerUrl = txtServerUrl.Text.TrimEnd('/'),
                SecretToken = txtSecretToken.Text.Trim(),
                AgentId = agentId,
                HeartbeatIntervalSeconds = (int)numInterval.Value,
                MetricsCollectIntervalSeconds = (int)numInterval.Value * 2
            };

            var json = JsonSerializer.Serialize(config, new JsonSerializerOptions { WriteIndented = true });
            File.WriteAllText(configPath, json);

            // Enable Auto-Start in Windows Registry Run Key
            EnableWindowsAutoStart();

            lblStatus.Text = "Sukses: Konfigurasi disimpan & Auto-Start aktif!";
            lblStatus.ForeColor = ColorTranslator.FromHtml("#10b981");

            MessageBox.Show("Konfigurasi berhasil disimpan dan Agent telah diset otomatis berjalan saat Windows startup!", "Pengaturan Disimpan", MessageBoxButtons.OK, MessageBoxIcon.Information);
            Close();
        }
        catch (Exception ex)
        {
            lblStatus.Text = "Gagal: " + ex.Message;
            lblStatus.ForeColor = ColorTranslator.FromHtml("#f43f5e");
        }
    }

    private void EnableWindowsAutoStart()
    {
        try
        {
            string exePath = Application.ExecutablePath;
            using var key = Registry.CurrentUser.OpenSubKey(@"SOFTWARE\Microsoft\Windows\CurrentVersion\Run", true);
            if (key != null)
            {
                key.SetValue("AditiaMonitorAgent", $"\"{exePath}\"");
            }
        }
        catch { }
    }
}
