from functions import (WindowManager, get_image_name, get_binary_image_name,
powerlaw_transform, uniform_darkening, skeletonise, sytox_enhancement,
brightfield_enhancement, LoG, unsharp, create_sytox_markers, create_sytox_borders,
watershed, watershed_light, adjust_brightfield_threshold, create_brightfield_markers,
create_brightfield_borders,)
import cv2
import numpy as np
"""
This is the main script that runs the algorithm.
The Window Manager is declared on line 25
if you wish to diplay any of the images at the end of the script (called by
wm.show()), simply type wm.add(<image_name>, <window_name>)
"""



"""
dict PATH: location of image folders relative to working directory
Entries: - Images => Original microsope images
         - GT_Worms => Ground Truth Human-Corrected binary images of all the worms
         - GT_Worm => Human-Corrected binary images of individual worms
"""
PATH = {"Images":   "BBBC010_v1_images/",
        "GT_Worms": "BBBC010_v1_foreground/",
        "GT_Worm":  "BBBC010_v1_foreground_eachworm/",
        }
wm = WindowManager()
# step 1 improve visibility/contrast
i_name = get_image_name(PATH["Images"], "B", 17, 1)
sytox_in = cv2.imread(i_name, cv2.IMREAD_UNCHANGED)
sytox_out = sytox_enhancement(sytox_in)

i_name = get_image_name(PATH["Images"], "B", 17, 2)
brightfield_in = cv2.imread(i_name, cv2.IMREAD_UNCHANGED)
brightfield_out = brightfield_enhancement(brightfield_in)
br_8ut = brightfield_out.copy()
br_8ut = br_8ut / 256
br_8ut = br_8ut.astype(np.uint8)
cv2.imwrite("brightfield_out.jpg", br_8ut, [cv2.IMWRITE_JPEG_QUALITY, 100])
wm.add(brightfield_out, "brightfield_out")

# step 2 binary thresholding
br_8u = brightfield_out.copy()
br_8u = br_8u / 256
br_8u = br_8u.astype(np.uint8)
cv2.fastNlMeansDenoising(br_8u, br_8u, 10, 30)
log = LoG(br_8u)
ret3, b_thresh = cv2.threshold(log, 0, 255, cv2.THRESH_BINARY_INV+cv2.THRESH_OTSU)

sy_8u = sytox_out.copy()
sy_8u = sy_8u / 256
sy_8u = sy_8u.astype(np.uint8)
unsharp = unsharp(sy_8u)
ret3, s_thresh = cv2.threshold(unsharp, 0, 255, cv2.THRESH_BINARY+cv2.THRESH_OTSU)

#Step 3 watershedding
#sytox
s_bgr = sy_8u.copy()
s_bgr = cv2.cvtColor(s_bgr, cv2.COLOR_GRAY2BGR)

triple_res = cv2.resize(s_thresh, None, fx=3, fy=3, interpolation=cv2.INTER_CUBIC)
sy_markers = create_sytox_markers(triple_res)
sy_border = create_sytox_borders(triple_res)

sy_bd_n = cv2.resize(sy_border, None, fx=1/3, fy=1/3, interpolation=cv2.INTER_CUBIC)
sy_skel_n = cv2.resize(sy_markers, None, fx=1/3, fy=1/3, interpolation=cv2.INTER_CUBIC)

sy_shed, sy_shed_inv, sy_ncc = watershed(sy_skel_n, sy_bd_n, s_bgr)
sy_shed_light = watershed_light(sy_skel_n, sy_bd_n, s_bgr)
sy_shed_color = cv2.applyColorMap(sy_shed_inv, cv2.COLORMAP_JET)

#brightfield
b_bgr = br_8u.copy()
b_bgr = cv2.cvtColor(b_bgr, cv2.COLOR_GRAY2BGR)

b_thresh_adjusted = adjust_brightfield_threshold(b_thresh)
b_markers = create_brightfield_markers(b_thresh_adjusted)
b_border = create_brightfield_borders(b_thresh_adjusted)

bd_n = cv2.resize(b_border, None, fx=1/3, fy=1/3, interpolation=cv2.INTER_CUBIC)
skel_n = cv2.resize(b_markers, None, fx=1/3, fy=1/3, interpolation=cv2.INTER_CUBIC)
b_shed, b_shed_inv, b_ncc = watershed(skel_n, bd_n, b_bgr)
b_shed_light = watershed_light(skel_n, bd_n, b_bgr)
b_shed_color = cv2.applyColorMap(b_shed_inv, cv2.COLORMAP_JET)

# step 4 rethreshing
ret3, sy_rethresh = cv2.threshold(sy_shed_light, 0, 255, cv2.THRESH_BINARY+cv2.THRESH_OTSU)
ret3, b_rethresh = cv2.threshold(b_shed_light, 0, 255, cv2.THRESH_BINARY+cv2.THRESH_OTSU)

#step 5: merge
binary_image = cv2.bitwise_or(sy_rethresh, b_rethresh)
# cv2.imwrite("binary_image.jpg", binary_image, [cv2.IMWRITE_JPEG_QUALITY, 100])
wm.add(binary_image, "binary_image")

#step 6: re-watershed:
dilated = cv2.dilate(binary_image, None, iterations=3)
border = dilated - cv2.erode(dilated, None)
markers = cv2.erode(binary_image, None, iterations=3)
skel = skeletonise(markers)
kernel = np.ones((2, 2), np.uint8)
skel = cv2.dilate(skel, kernel, iterations=1)
watershed, inv, ncc = watershed(skel, border, b_bgr)
#step 7: number of worms
print("number of worms =~", ncc)

#step 8 label
watershed_color = cv2.applyColorMap(b_shed_inv, cv2.COLORMAP_JET)
# cv2.imwrite("watershed.jpg", watershed_color, [cv2.IMWRITE_JPEG_QUALITY, 100])
wm.add(watershed_color, "watershed_color")

# step9 compare
i_name = get_binary_image_name(PATH["GT_Worms"], "B", 17)
gt_binary = cv2.imread(i_name, cv2.IMREAD_GRAYSCALE)
gt_hist = cv2.calcHist([gt_binary],[0],None,[256],[0,256])
my_hist = cv2.calcHist([binary_image],[0],None,[256],[0,256])
print(gt_binary.shape)
print(binary_image.shape)

correlation = cv2.compareHist(gt_hist, my_hist, cv2.HISTCMP_CORREL)
chi_squared = cv2.compareHist(gt_hist, my_hist, cv2.HISTCMP_CHISQR)
bhattacharyya = cv2.compareHist(gt_hist, my_hist, cv2.HISTCMP_BHATTACHARYYA)
intersection = cv2.compareHist(gt_hist, my_hist, cv2.HISTCMP_INTERSECT)

print("Correlation =", correlation)
print("Chi Squared =", chi_squared)
print("Bhattacharyya =", bhattacharyya)
print("Intersection =", intersection)

wm.add(gt_binary, "gt_binary")
# wm.add(binary_image, "binary_image")
wm.show()
