<?php
session_start();

include "../../dbconnect.php";

if (
  !isset($_SESSION["user_id"]) ||
  $_SESSION["user_role"] !== "kplbhq"
) {
  header("Location: ../login.php");
  exit();
}

$user_id = $_SESSION["user_id"];
$username = $_SESSION["user_name"];
$role = $_SESSION["user_role"];

// Fetch reports for this villager ONLY
$sql = "
    SELECT
        vr.*,
        v.user_name AS villager_name,
        k.user_name AS ketua_name
    FROM villager_report vr
    JOIN tbl_users v ON vr.villager_id = v.user_id
    JOIN tbl_users k ON vr.ketua_id = k.user_id
    ORDER BY vr.report_date ASC
";

$villagerreportresult = mysqli_query($conn, $sql);

//penghulu report data
$sql = "
    SELECT
        r.*,
        k.user_name AS penghulu_name
    FROM penghulu_report r
    JOIN tbl_users k ON r.penghulu_id = k.user_id
    ORDER BY r.created_at DESC
";

$penghulureportresult = mysqli_query($conn, $sql);

//ketua kampung report data
$sql = "
    SELECT
        r.*,
        k.user_name AS ketua_name
    FROM ketua_report r
    JOIN tbl_users k ON r.ketua_id = k.user_id
    ORDER BY r.created_at DESC
";
$ketuareportresult = mysqli_query($conn, $sql);

//aid distribution
$sql = "
    SELECT
        r.*,
        k.user_name AS ketua_name
    FROM pejabatdaerah_aid_distribution r
    JOIN tbl_users k ON r.pejabatdaerah_id = k.user_id
    ORDER BY r.created_at DESC
";
$pejabatdaerahresult = mysqli_query($conn, $sql);





$report_sql = "SELECT r.latitude, r.longitude, r.report_title, r.report_type, r.report_status,
                u.user_name AS submitted_by
                FROM villager_report r
                JOIN tbl_users u ON
                r.villager_id = u.user_id
                WHERE r.report_status = 'Pending'";
$report_result = mysqli_query($conn, $report_sql);
$reports = [];
while ($row = mysqli_fetch_assoc($report_result)) {
  $row["type"] = "report";
  $reports[] = $row;
}

//Alerts on map
$sos_sql = "SELECT s.latitude, s.longitude, s.sos_status, u.user_name AS sent_by
            FROM sos_villager s
            JOIN tbl_users u ON s.villager_id = u.user_id
            WHERE s.sos_status = 'Sent'";
$sos_result = mysqli_query($conn, $sos_sql);
$sos = [];
while ($row = mysqli_fetch_assoc($sos_result)) {
  $row["type"] = "sos";
  $sos[] = $row;
}

// Combine
$allPins = array_merge($reports, $sos);
$pinreports_json = json_encode($allPins);



?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Pejabat Daerah Dashboard - DVMD</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="../../css/style_villager_dashboard.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>

<style>
  .btn-with-badge {
    position: relative;
    display: inline-block;
    padding: 10px 20px;
    background-color: #1e40af;
    color: white;
    text-decoration: none;
    border-radius: 5px;
  }

  .btn-with-badge .badge {
    position: absolute;
    top: -5px;
    right: -10px;
    background-color: red;
    color: white;
    border-radius: 50%;
    padding: 5px 10px;
    font-size: 12px;
  }

  #disastercommandform {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
  }

  .notificationformpejabatdaerah {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    width: 400px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  }

  .notificationformpejabatdaerah h2 {
    text-align: center;
    margin: 0 auto;
  }

  .notificationformpejabatdaerah label {
    display: block;
    margin-bottom: 5px;
  }

  .notificationformpejabatdaerah input,
  .notificationformpejabatdaerah select,
  .notificationformpejabatdaerah textarea {
    width: 100%;
    padding: 8px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;

  }

  .notificationformpejabatdaerah .btn {
    background-color: #4CAF50;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
  }

  #aiddistributionform {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
  }

  .aiddistributionform {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    width: 400px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  }

  .aiddistributionform h2 {
    text-align: center;
    margin: 0 auto;
  }

  .aiddistributionform label {
    display: block;
    margin-bottom: 5px;
  }

  .aiddistributionform input,
  .aiddistributionform select,
  .aiddistributionform textarea {
    width: 100%;
    padding: 8px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;

  }

  .aiddistributionform .btn {
    background-color: #4CAF50;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
  }

  .table-container {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  }

  table {
    width: 100%;
    border-collapse: collapse;
  }

  th,
  td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    text-align: left;
  }

  th {
    background: #1e40af;
    color: white;
  }

  .status-pending {
    color: orange;
    font-weight: bold;
  }

  .status-approved {
    color: green;
    font-weight: bold;
  }

  .status-rejected {
    color: red;
    font-weight: bold;
  }

  .back-btn {
    display: inline-block;
    margin-bottom: 15px;
    text-decoration: none;
    color: #1e40af;
    font-weight: bold;
  }
</style>

<body>
  <div class="dashboard">

    <!-- Sidebar -->
    <aside class="sidebar">
      <h2>Pejabat Daerah</h2>
      <ul>
        <li><a href="pejabatdaerah_dashboard.php"><i class="fa fa-home"></i> Home</a></li>
        <li><a href="../../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
      </ul>
    </aside>

    <!-- Main -->
    <main class="main">

      <!-- Header -->
      <div class="header">
        <h1>Welcome, <?php echo $username; ?> !</h1>
        <p>Digital Village Management Dashboard (DVMD)</p>
      </div>

      <!-- Content -->
      <section class="content">

        <!-- Villager Reports Table -->
        <div class="table-container">
          <h2>Villager Reports</h2>
          <table>
            <tr>
              <th>Id</th>
              <th>Villager</th>
              <th>Title</th>
              <th>Type</th>
              <th>Description</th>
              <th>Date</th>
              <th>Location</th>
              <th>Ketua Kampung</th>
              <th>Status</th>

            </tr>

            <?php if (mysqli_num_rows($villagerreportresult) > 0): ?>
              <?php $i = 1;
              while ($row = mysqli_fetch_assoc($villagerreportresult)): ?>
                <tr>
                  <td><?= $i++ ?></td>
                  <td><?= htmlspecialchars($row['report_title']) ?></td>
                  <td><?= htmlspecialchars($row['villager_name']) ?></td>
                  <td><?= htmlspecialchars($row['report_type']) ?></td>
                  <td><?= htmlspecialchars($row['report_desc']) ?></td>
                  <td><?= htmlspecialchars($row['report_date']) ?></td>
                  <td><?= htmlspecialchars($row['report_location']) ?></td>
                  <td><?= htmlspecialchars($row['ketua_name']) ?></td>
                  <td class="status-<?= strtolower($row['report_status']) ?>">
                    <?= htmlspecialchars($row['report_status']) ?>
                  </td>



                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" style="text-align:center;">No reports submitted yet</td>
              </tr>
            <?php endif; ?>
          </table>

      </section>
      <section>
        <!-- Ketua Kampung Report List -->
        <div class="table-container">
          <h2>Ketua Kampung Reports</h2>
          <table>
            <tr>
              <th>No</th>
              <th>Ketua Kampung</th>
              <th>Title</th>
              <th>Description</th>
              <th>Location</th>
              <th>Status</th>
              <th>Feedback</th>
              <th>Date</th>

            </tr>


            <?php if (mysqli_num_rows($ketuareportresult) > 0): ?>
              <?php
              $i = 1;
              while (
                $row = mysqli_fetch_assoc(
                  $ketuareportresult,
                )
              ): ?>
                <tr>
                  <td><?= $i++ ?></td>
                  <td><?= htmlspecialchars(
                        $row["ketua_name"],
                      ) ?>
                  </td>
                  <td><?= htmlspecialchars(
                        $row["report_title"],
                      ) ?>
                  </td>
                  <td><?= htmlspecialchars(
                        $row["report_desc"],
                      ) ?>
                  </td>
                  <td><?= htmlspecialchars(
                        $row["report_location"],
                      ) ?>
                  </td>
                  <td
                    class="status-<?= strtolower(
                                    $row["report_status"],
                                  ) ?>">
                    <?= htmlspecialchars(
                      $row["report_status"],
                    ) ?>
                  </td>
                  <td><?= htmlspecialchars(
                        $row["report_feedback"],
                      ) ?>
                  </td>
                  <td><?= htmlspecialchars(
                        $row["created_at"],
                      ) ?>
                  </td>
                  <td>
                    <?php if (
                      $row["report_status"] ===
                      "Pending"
                    ): ?>

                    <?php endif; ?>
                  </td>

                </tr>
              <?php endwhile;
              ?>
            <?php else: ?>
              <tr>
                <td colspan="6" style="text-align:center;">No reports submitted yet</td>
              </tr>
            <?php endif; ?>
          </table>
      </section>
      <section>
        <!--Aid Distribution Report Table-->
        <div class="table-container">
          <h2>Aid Distribution Reports</h2>
          <table>
            <tr>
              <th>ID</th>
              <th>Pejabat Daerah ID</th>
              <th>Penghulu ID</th>
              <th>Type</th>
              <th>Title</th>
              <th>Description</th>
              <th>Location</th>
              <th>Date</th>
            </tr>


            <?php if (mysqli_num_rows($pejabatdaerahresult) > 0): ?>
              <?php
              $i = 1;
              while (
                $row = mysqli_fetch_assoc(
                  $pejabatdaerahresult,
                )
              ): ?>
                <tr>
                  <td><?= $i++ ?></td>
                  <td><?= htmlspecialchars(
                        $row["pejabatdaerah_id"],
                      ) ?>
                  </td>
                  <td><?= htmlspecialchars(
                        $row["penghulu_id"],
                      ) ?>
                  </td>
                  <td><?= htmlspecialchars(
                        $row["aid_type"],
                      ) ?>
                  </td>
                  <td><?= htmlspecialchars(
                        $row["distribution_title"],
                      ) ?>
                  </td>
                  <td>
                    <?= htmlspecialchars(
                      $row["distribution_desc"],
                    ) ?>
                  </td>
                  <td><?= htmlspecialchars(
                        $row["distribution_location"],
                      ) ?>
                  </td>
                  <td><?= htmlspecialchars(
                        $row["created_at"],
                      ) ?>
                  </td>


                </tr>
              <?php endwhile;
              ?>
            <?php else: ?>
              <tr>
                <td colspan="10" style="text-align:center;">No reports submitted yet</td>
              </tr>
            <?php endif; ?>
          </table>
      </section>
      <section>
        <!--Penghulu Report Table-->
        <div class="table-container">
          <h2>Penghulu Reports</h2>
          <table>
            <tr>
              <th>No</th>
              <th>Penghulu</th>
              <th>Title</th>
              <th>Description</th>
              <th>Location</th>
              <th>Status</th>
              <th>Feedback</th>
              <th>Date</th>
            </tr>


            <?php if (mysqli_num_rows($penghulureportresult) > 0): ?>
              <?php
              $i = 1;
              while (
                $row = mysqli_fetch_assoc(
                  $penghulureportresult,
                )
              ): ?>
                <tr>
                  <td><?= $i++ ?></td>
                  <td><?= htmlspecialchars(
                        $row["penghulu_name"],
                      ) ?>
                  </td>
                  <td><?= htmlspecialchars(
                        $row["report_title"],
                      ) ?>
                  </td>
                  <td><?= htmlspecialchars(
                        $row["report_desc"],
                      ) ?>
                  </td>
                  <td><?= htmlspecialchars(
                        $row["report_location"],
                      ) ?>
                  </td>
                  <td
                    class="status-<?= strtolower(
                                    $row["report_status"],
                                  ) ?>">
                    <?= htmlspecialchars(
                      $row["report_status"],
                    ) ?>
                  </td>
                  <td><?= htmlspecialchars(
                        $row["report_feedback"],
                      ) ?>
                  </td>
                  <td><?= htmlspecialchars(
                        $row["created_at"],
                      ) ?>
                  </td>
                  <td>
                    <?php if (
                      $row["report_status"] ===
                      "Pending"
                    ): ?>
                      

                    <?php else: ?>

                    <?php endif; ?>
                  </td>

                </tr>
              <?php endwhile;
              ?>
            <?php else: ?>
              <tr>
                <td colspan="10" style="text-align:center;">No reports submitted yet</td>
              </tr>
            <?php endif; ?>
          </table>
      </section>

      <section>
        <!-- Map -->
        <div class="card">
          <h3>Incident Map</h3>
          <p>Track emergencies using GPS/maps.</p>
          <div id="incident-map" class="map-placeholder" onclick="openFullMap()"></div>
        </div>

      </section>
    </main>
  </div>

  <!-- Map Modal -->
  <div id="mapModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999;">
    <div style="background:#fff; width:90%; max-width:600px; height:400px; margin:50px auto; padding:10px;">
      <h3>Click on map to select location</h3>
      <div id="map" style="height:300px;"></div>
      <button onclick="closeMap()">Done</button>
    </div>
  </div>

  <!-- Fullscreen Map Modal -->
  <div id="fullMapModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:9999;">
    <div style="position:relative; width:100%; height:100%;">
      <span
        style="position:absolute; top:10px; right:20px; font-size:30px; color:white; cursor:pointer; z-index:1000;"
        onclick="closeFullMap()">&times;</span>
      <div id="fullIncidentMap" style="width:100%; height:100%;"></div>
    </div>
  </div>





</body>

<!-- Map Script -->
<script>
  var greenIcon = L.icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34]
  });

  var redIcon = L.icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34]
  });

  // Pins from PHP
  var pins = <?php echo $pinreports_json; ?>;

  // ---- Incident Map on dashboard ----
  let incidentMap = L.map('incident-map').setView([6.4432, 100.2056], 13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
  }).addTo(incidentMap);

  // Add pins
  pins.forEach(function(pin) {
    if (pin.latitude && pin.longitude) {
      let icon = pin.type === 'report' ? greenIcon : redIcon;
      let popup = pin.type === 'report' ?
        `<b>Report: ${pin.report_type}</b><br>Title: ${pin.report_title}<br>Status: ${pin.report_status}<br>Submitted by: ${pin.submitted_by}` :
        `<b>SOS Alert</b><br>Status: ${pin.sos_status}<br>Sent by: ${pin.sent_by}`;

      L.marker([pin.latitude, pin.longitude], {
          icon: icon
        })
        .addTo(incidentMap)
        .bindPopup(popup);
    }
  });

  // ---- Fullscreen Map ----
  let fullMap; // global variable
  function openFullMap() {
    const modal = document.getElementById('fullMapModal');
    modal.style.display = 'block';

    setTimeout(() => {
      // Remove old map if exists
      if (fullMap) fullMap.remove();

      // Initialize map
      fullMap = L.map('fullIncidentMap');
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
      }).addTo(fullMap);

      // Add all pins
      pins.forEach(function(pin) {
        if (pin.latitude && pin.longitude) {
          let icon = pin.type === 'report' ? greenIcon : redIcon;
          let popup = pin.type === 'report' ?
            `<b>Report: ${pin.report_type}</b><br>Title: ${pin.report_title}<br>Status: ${pin.report_status}<br>Submitted by: ${pin.submitted_by}` :
            `<b>SOS Alert</b><br>Status: ${pin.sos_status}<br>Sent by: ${pin.sent_by}`;

          L.marker([pin.latitude, pin.longitude], {
              icon: icon
            })
            .addTo(fullMap)
            .bindPopup(popup);
        }
      });

      // Zoom to fit all pins
      let group = L.featureGroup(pins.map(pin => L.marker([pin.latitude, pin.longitude])));
      fullMap.fitBounds(group.getBounds().pad(0.2));

      // Fix map size
      fullMap.invalidateSize();
    }, 100); // small delay ensures modal is visible
  }

  function closeFullMap() {
    document.getElementById('fullMapModal').style.display = 'none';
    if (fullMap) fullMap.remove();
  }

  // ---- Map Picker for Report / SOS ----
  let mapPicker, reportMarker, sosMarker;

  function openMapPicker(type) {
    document.getElementById("mapModal").style.display = "block";

    setTimeout(() => {
      if (mapPicker) mapPicker.remove();

      mapPicker = L.map('map').setView([6.4432, 100.2056], 13);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
      }).addTo(mapPicker);

      mapPicker.on('click', function(e) {
        let lat = e.latlng.lat;
        let lng = e.latlng.lng;

        if (type === 'report') {
          if (reportMarker) reportMarker.setLatLng(e.latlng);
          else reportMarker = L.marker(e.latlng, {
            icon: greenIcon
          }).addTo(mapPicker);

          document.getElementById("latitude").value = lat;
          document.getElementById("longitude").value = lng;

        } else if (type === 'sos') {
          if (sosMarker) sosMarker.setLatLng(e.latlng);
          else sosMarker = L.marker(e.latlng, {
            icon: redIcon
          }).addTo(mapPicker);

          document.getElementById("sos_latitude").value = lat;
          document.getElementById("sos_longitude").value = lng;
        }
      });

      mapPicker.invalidateSize();
    }, 100);
  }

  function closeMap() {
    document.getElementById("mapModal").style.display = "none";
  }
</script>

</html>